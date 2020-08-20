from django.shortcuts import render
from django.contrib.auth.models import User
import contextlib, io
import requests
import json



def api_doc(request):
    context = dict()
    documentation = "This is API Documentation: Type '?param' + get_users/get_last_films/easter"
    context['filler'] = documentation
    param = request.GET.get('param', '')
    if param != '':
        if param == 'get_users':
            users = [str(user) for user in User.objects.all()]
            context['filler'] = users
        elif param == 'get_last_films':
            r = requests.get('https://yts.mx/api/v2/list_movies.json')
            movies = json.loads(r.text)['data']['movies']
            context['filler'] = movies
        elif param == 'easter':
            zen = io.StringIO()
            with contextlib.redirect_stdout(zen):
                import this
            context['filler'] = zen.getvalue()
        
    return render(request, 'api/documentation.html', context)
