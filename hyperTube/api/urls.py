from django.urls import path
from . import views
import re

urlpatterns = [
    path('api/', views.api_doc, name = 'apiDoc'),
]