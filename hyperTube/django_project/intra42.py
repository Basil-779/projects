from requests import HTTPError

from six.moves.urllib.parse import urljoin

from social_core.backends.oauth import BaseOAuth2


class Intra42OAuth2(BaseOAuth2):
    """Github OAuth authentication backend"""
    name = 'intra42'
    AUTHORIZATION_URL = 'https://api.intra.42.fr/oauth/authorize'
    ACCESS_TOKEN_URL = 'https://api.intra.42.fr/oauth/token'
    REFRESH_TOKEN_URL = 'https://api.intra.42.fr/oauth/token'
    ACCESS_TOKEN_METHOD = 'POST'
    DEFAULT_SCOPE = ['public']
    EXTRA_DATA = [
        ('id', 'id'),
        ('expires', 'expires'),
    ]

    def get_user_details(self, response):
        """Return user details from GitHub account"""
        return {'username': response.get('login'),
                'email': response.get('email') or '',
                'first_name': response.get('first_name')}

    def user_data(self, access_token, *args, **kwargs):
        """Loads user data from service"""
        url = 'https://api.intra.42.fr/v2/me'
        return self.get_json(url, headers={'Authorization': 'Bearer {0}'.format(access_token)})

    
