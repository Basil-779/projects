import requests
import json

def sortFilter(moviesToParse, min_rating,
                max_rating, min_year, max_year):
    gen = set()
    movies1 = list()
    movies = list()

    for movie in moviesToParse:
        if float(movie['rating']) >= float(min_rating) and float(movie['rating']) <= float(max_rating):
            movies1.append(movie)

    for movie in movies1:
        if int(movie['year']) >= int(min_year) and int(movie['year']) <= int(max_year):
            movies.append(movie)

    for movie in movies:
        for g in movie['genres']:
            gen.add(g)

    return [movies, gen]