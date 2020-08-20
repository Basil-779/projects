from django.db import models
from django.utils import timezone
from django.contrib.auth.models import User
from django.urls import reverse


class Post(models.Model):
    film_id = models.CharField(max_length=100)
    content = models.TextField()
    date_posted = models.DateTimeField(default=timezone.now)
    author = models.ForeignKey('auth.User', on_delete=models.CASCADE)

    def __str__(self):
        return (self.film_id + ' ' + self.content + ' ' + str(self.author) + ' ' + str(self.date_posted) + ' ' + str(self.author.profile.image))

    def get_absolute_url(self):
        return reverse('post-detail', kwargs={'pk': self.pk})

class Visited(models.Model):
    film_id = models.CharField(max_length=100)
    visitor = models.ForeignKey('auth.User', on_delete=models.CASCADE)