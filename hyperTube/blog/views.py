from django.shortcuts import render, get_object_or_404
from django.contrib.auth.mixins import LoginRequiredMixin, UserPassesTestMixin
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User
from django.contrib import messages
from datetime import datetime
from django import forms
from . sort_filter import *
from . get_films import *
from django.core.mail import send_mail
from django.http import HttpResponse
from django.views.generic import (
    ListView,
    DetailView,
    CreateView,
    UpdateView,
    DeleteView
)
from users.models import Profile
from .models import Post
from .models import Visited
import requests
import json
import glob
from django.utils.http import urlquote as django_urlquote
from django.utils.http import urlencode as django_urlencode
from django.utils.translation import gettext, gettext_lazy as _
import os
from . torStream import *
import threading

@login_required
def home(request):
    context = dict()
    if request.method == "POST":
        query_term = request.POST.get('filmname', 1)
        sort_by = request.POST.get('sort_by', 'rating')
        order_by = request.POST.get('order_by', 'desc')
        page_number = request.POST.get('page_number', 1)
        year_range = request.POST.get('year_range', 0)
        rating_range = request.POST.get('rating_range', 0)

        if (year_range != 0):
            min_year = year_range.split(",")[0]
            max_year = year_range.split(",")[1]
        else:
            min_year = 1900
            max_year = 2020

        if (rating_range != 0):
            min_rating = rating_range.split(",")[0]
            max_rating = rating_range.split(",")[1]
        else:
            min_rating = 0
            max_rating = 10

        try:
            movies = getFilms(query_term, sort_by, order_by)
        except requests.exceptions.ConnectionError:
            movies = ''
            mes = _("Connection error. Try again.")
            messages.warning(request, mes)
        if (len(movies) > 0):
            movies = sortFilter(movies, min_rating, max_rating, min_year, max_year)[0]
            for movie in movies:
                visit = Visited.objects.filter(film_id = movie['id'], visitor = request.user)
                if not visit.exists():
                    movie.update(is_visited = '0')
                else:
                    movie.update(is_visited = '1')
            genres = sortFilter(movies, min_rating, max_rating, min_year, max_year)[1]
            if (len(movies) % 20 == 0):
                page_count = int(len(movies) / 20)
            else:
                page_count = int(len(movies) / 20 + 1)
            page_list = [i for i in range(1, page_count + 1)]
            context = {
                'movies': movies[((int(page_number) - 1) * 20) : (int(page_number) * 20)],
                'length': len(movies),
                'query_term': query_term,
                'sort_by': sort_by,
                'order_by': order_by,
                'min_rating': min_rating,
                'max_rating': max_rating,
                'min_year': min_year,
                'max_year': max_year,
                'page_number': page_number,
                'page_count': page_count,
                'page_list': page_list,
                'genres': genres,
            }
        else:
            try:
                r = requests.get('https://yts.mx/api/v2/list_movies.json', {'sort_by': 'rating'})
                movies = json.loads(r.text)['data']['movies']
            except requests.exceptions.ConnectionError:
                movies = ''
            context = {
                'movies': movies,
            }
    return render(request, 'blog/home.html', context)

def movie_list(request, sort_by):
    r = requests.get('https://yts.mx/api/v2/list_movies.json', params={'sort_by': sort_by})
    movies = json.loads(r.text)['data']['movies']
    context = {
        'posts': Post.objects.all(),
        'movies': movies
    }
    return render(request, 'blog/home.html', context)

def movie_list_genre(request, genre):
    r = requests.get('https://yts.mx/api/v2/list_movies.json', params={'genre': genre})
    movies = json.loads(r.text)['data']['movies']
    context = {
        'posts': Post.objects.all(),
        'movies': movies
    }
    return render(request, 'blog/home.html', context)

@login_required
def movie(request, id):
    if os.path.isdir('/code/blog/{}'.format(request.user.username)):
        if os.path.isfile('/code/blog/{}/error'.format(request.user.username)):
	        os.remove('/code/blog/{}/error'.format(request.user.username))
        if os.path.isfile('/code/blog/{}/mpd'.format(request.user.username)):
            os.remove('/code/blog/{}/mpd'.format(request.user.username))
        os.rmdir('/code/blog/{}'.format(request.user.username))

    if os.path.isfile('/code/blog/error'):
        os.remove('/code/blog/error')
    if os.path.isfile('/code/blog/mpd'):
        os.remove('/code/blog/mpd')

    try: 
        r = requests.get('https://yts.mx/api/v2/movie_details.json', params={'movie_id': id})
        movie = json.loads(r.text)['data']['movie']

        trackers = {
            "udp://open.demonii.com:1337/announce",
            "udp://tracker.openbittorrent.com:80",
            "udp://tracker.coppersurfer.tk:6969",
            "udp://glotorrents.pw:6969/announce",
            "udp://tracker.opentrackr.org:1337/announce",
            "udp://torrent.gresille.org:80/announce",
            "udp://p4p.arenabg.com:1337",
            "udp://tracker.leechers-paradise.org:6969"
        }

        torrent_720_peers = ''
        torrent_1080_peers = ''

        torrents = dict()
        for torrent in movie['torrents']:
            if torrent['quality'] == '720p':
                torrents['720'] = torrent
                torrent_720_peers = torrent['peers']
            elif torrent['quality'] == '1080p' and torrent['peers'] > 5:
                torrents['1080'] = torrent
                torrent_1080_peers = torrent['peers']

        torrent_720 = ''
        torrent_1080 = ''
        mpd_720 = ''
        mpd_1080 = ''
        if '720' in torrents.keys():
            torrent_720 = 'magnet:?xt=urn:btih:' + torrents['720']['hash'] + '&dn=' + django_urlquote(movie['title']) + '&tr='.join(django_urlquote(str(e)) for e in trackers)
            mpd_720 = torrents['720']['hash'] + '.mpd'
        if '1080' in torrents.keys():
            torrent_1080 = 'magnet:?xt=urn:btih:' + torrents['1080']['hash'] + '&dn=' + django_urlquote(movie['title']) + '&tr='.join(django_urlquote(str(e)) for e in trackers)
            mpd_1080 = torrents['1080']['hash'] + '.mpd'

        movie_1 = movie['title'][0:movie['title'].rfind("'")]

        symbols = ':+%&*?'
        movie_title = movie['title_english'] + '.' + str(movie['year'])
        for symbol in symbols:
            movie_title = movie_title.replace(symbol, '*')
        movie_title = movie_title.replace(' ', '.')

        movie_path = glob.glob('/code/media/**/{}'.format('*' + movie_title + '*'))

        movie_path_720 = ''
        movie_path_1080 = ''
        movie_sub_720 = ''
        movie_sub_1080 = ''
        movie_sub_720_rus = ''
        movie_sub_1080_rus = ''
        """if len(movie_path) > 0:
            for path in movie_path:
                if '720p' in path:
                    if os.path.getsize(path) >= int(torrents['720']['size_bytes']):
                        movie_path_720 = path[5:]
                elif '1080p' in path:
                    if os.path.getsize(path) >= int(torrents['1080']['size_bytes']):
                        movie_path_1080 = path[5:]"""

        if len(movie_path) > 0:
            for path in movie_path:
                if '720p' in path:
                    if os.path.getsize(path) >= int(torrents['720']['size_bytes']):
                        movie_path_720 = path[5:]
                    elif '.vtt' in path:
                        if 'rus' in path:
                            movie_sub_720_rus = path[5:]
                        elif 'rus' not in path:
                            movie_sub_720 = path[5:]
                elif '1080p' in path:
                    if os.path.getsize(path) >= int(torrents['1080']['size_bytes']):
                        movie_path_1080 = path[5:]
                    elif '.vtt' in path:
                        if 'rus' in path:
                            movie_sub_1080_rus = path[5:]
                        elif 'rus' not in path:
                            movie_sub_1080 = path[5:]

        if movie_path_720 == '':
            movie_sub_720 = ''
            movie_sub_720_rus = ''
        if movie_path_1080 == '':
            movie_sub_1080 = ''
            movie_sub_1080_rus = ''

        visits = Visited.objects.filter(film_id = id, visitor=request.user)
        if not visits:
            visit = Visited(film_id = id, visitor=request.user)
            visit.save()

        context = {
            'torrent_720': torrent_720,
            'torrent_1080': torrent_1080,
            'torrent_720_peers': torrent_720_peers,
            'torrent_1080_peers': torrent_1080_peers,
            'movie': movie,
            'mpd_720': mpd_720,
            'mpd_1080': mpd_1080,
            'posts': Post.objects.filter(film_id=id),
            'movie_path_720': movie_path_720,
            'movie_path_1080': movie_path_1080,
            'movie_sub_720': movie_sub_720,
            'movie_sub_1080': movie_sub_1080,
            'movie_sub_720_rus': movie_sub_720_rus,
            'movie_sub_1080_rus': movie_sub_1080_rus
        }

        torrent = ''
        param = request.POST.get('quality', '')
        name = request.user.username
        path = os.path.join('/code/blog', name)
        if param == '720':
            t = threading.Thread(target=TorrentStreamer().get_parallel_magnets, args=[torrent_720, -1, 5, "vlc", name])
            t.setDaemon(False)
            t.start()
            
            
            os.mkdir(path)
            while True:
                time.sleep(5)
                if os.path.isfile('/code/blog/{}/mpd'.format(name)):
                    time.sleep(15)
                    if os.path.isfile('/code/blog/{}/error'.format(name)):
                        return HttpResponse('Error')
                    else:
                        return HttpResponse('Success')

        elif param == '1080':
            t = threading.Thread(target=TorrentStreamer().get_parallel_magnets, args=[torrent_1080, -1, 5, "vlc", name])
            t.setDaemon(False)
            t.start()
            os.mkdir(path)
            while True:
                time.sleep(15)
                if os.path.isfile('/code/blog/{}/mpd'.format(name)):
                    time.sleep(5)
                    if os.path.isfile('/code/blog/{}/error'.format(name)):
                        return HttpResponse('Error')
                    else:
                        return HttpResponse('Success')

        comment = request.POST.get('comment', '')
        if comment != '':
            messages.success(request, "Comment has been added.")
            comm = Post(film_id = id, content = request.POST['comment'], date_posted = datetime.now(), author=request.user)
            comm.save()

    except requests.exceptions.ConnectionError:
            mes = _("Connection error. Try again.")
            messages.warning(request, mes)
            context = {}
    return render(request, 'blog/movie_detail.html', context)

class PostListView(ListView):
    model = Post
    template_name = 'blog/home.html'  # <app>/<model>_<viewtype>.html
    context_object_name = 'posts'
    ordering = ['-date_posted']
    paginate_by = 5


class UserPostListView(ListView):
    model = Post
    template_name = 'blog/user_posts.html'  # <app>/<model>_<viewtype>.html
    context_object_name = 'posts'
    paginate_by = 5

    #def get_queryset(self):
        #user = get_object_or_404(User, username=self.kwargs.get('username'))
        #return Post.objects.filter(author=user).order_by('-date_posted')
    def get_queryset(self):
        user = get_object_or_404(User, username=self.kwargs.get('username'))
        posts = Post.objects.filter(author=user).order_by('-date_posted')
        p_form = Profile.objects.filter(user_id=user)
        return posts


class PostDetailView(DetailView):
    model = Post


class PostCreateView(LoginRequiredMixin, CreateView):
    model = Post
    fields = ['title', 'content']

    def form_valid(self, form):
        form.instance.author = self.request.user
        return super().form_valid(form)


class PostUpdateView(LoginRequiredMixin, UserPassesTestMixin, UpdateView):
    model = Post
    fields = ['title', 'content']

    def form_valid(self, form):
        form.instance.author = self.request.user
        return super().form_valid(form)

    def test_func(self):
        post = self.get_object()
        if self.request.user == post.author:
            return True
        return False


class PostDeleteView(LoginRequiredMixin, UserPassesTestMixin, DeleteView):
    model = Post
    success_url = '/'

    def test_func(self):
        post = self.get_object()
        if self.request.user == post.author:
            return True
        return False

class ProfileUpdateForm(forms.ModelForm):
    class Meta:
        model = Profile
        fields = ['image', 'bio']

def SeePosts(request, username):
    #user = get_object_or_404(User, username)
    posts = Post.objects.filter(author=request.user).order_by('-date_posted')
    p_form = ProfileUpdateForm(instance=request.user.profile)
    context = {
        'p_form': p_form,
        'posts': posts
    }
    return render(request, 'blog/user_posts.html', context)

def about(request):
    param = request.POST.get('quality', '')
    if param != '':
        return HttpResponse(param)
    return render(request, 'blog/about.html', {'title': 'About'})
