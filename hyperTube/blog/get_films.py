import requests
import json

def getFilms(query, sort_by, order_by):
    search = {
        'query_term' : query,
        'sort_by' : sort_by,
        'order_by' : order_by
    }
    try:
        r = requests.get('https://yts.mx/api/v2/list_movies.json', search)
        count = json.loads(r.text)['data']['movie_count']
    except requests.exceptions.ConnectionError:
        raise requests.exceptions.ConnectionError

    if (count % 20 != 0):
        pages = count / 20 + 1
    else:
        pages = count / 20
    movies1 = list()
    i = 1

    while (pages >= i):
        search = {
            'query_term' : query,
            'sort_by' : sort_by,
            'order_by' : order_by,
            'page' : i
        }
        r = requests.get('https://yts.mx/api/v2/list_movies.json', search)
        for movie in json.loads(r.text)['data']['movies']:
            movies1.append(movie)
        i += 1
    return movies1