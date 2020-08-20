#!/usr/bin/env python

"""
    Simple torrent streaming module
"""

from concurrent.futures import ThreadPoolExecutor
import mimetypes
from urllib.parse import urlparse
from urllib.parse import parse_qs
import re


import libtorrent as lt

import subprocess
import threading
import argparse
import logging
import time
import os

BUFF_PERCENT = 5
logging.basicConfig(
    level=logging.DEBUG,
    format='[%(levelname)s] (%(threadName)-10s) %(message)s'
)

def get_hash(magnet_):
    magnet_p = parse_qs(urlparse(magnet_).query)
    return re.match('urn:btih:(.*)', magnet_p['xt'][0]).group(1)


def get_media_files(magnet):

    handle = magnet['handle']
    tinfo = handle.get_torrent_info()
    maxfs = 0
    for fle in tinfo.files():
        if (maxfs < fle.size):
            maxfs = fle.size
            path = fle.path
    print(path)
    return path


def make_status_readable(magnet):

    status = magnet['handle'].status()
    if not status:
        return "None"
    return '%.2f%% (d: %.1f kb/s up: %.1f kB/s p: %d)\r' % (
        status.progress * 100, status.download_rate / 1000,
        status.upload_rate / 1000,
        status.num_peers
    )


def make_download_status(magnet):

    def get_status(pieces, piece, downloading):
        """
            Nicely looking status.
        """
        status = "[ ]"
        if pieces[piece] is True:
            status = "[#]"
        if piece in downloading:
            status = "[D]"
        return status

    queue = magnet['handle'].get_download_queue()
    pieces = magnet['handle'].status().pieces

    downloading = [piece['piece_index'] for piece in queue]
    pieces = dict(enumerate(pieces))
    return [get_status(pieces, piece, downloading) for piece in pieces]


def set_streaming_priorities(handle):

    #handle.set_sequential_download(True)
    
    pieces = dict(enumerate(handle.status().pieces))
    next_pieces = [key for key, val in pieces.iteritems() if val][:3]
    for piece in next_pieces:
        handle.piece_priority(piece, 7)


def is_playable(file_, handle):

    if not file_:
        return False
    status_ = make_status_readable(handle.status())
    return status_.progress > BUFF_PERCENT  # Wait until we have 1/5

def stream_conditions_met(magnet):

    if magnet['stream_ratio'] == -1:
        return False
    elif magnet['stream_ratio'] == 0:
        return True
    else:
        # TODO, seed ratio
        raise NotImplementedError()

def play_conditions_met(magnet):

    if not magnet['file']:
        return False

    if magnet['play_ratio'] == -1:
        return False

    return magnet['play_ratio'] <= magnet['handle'].status().progress * 100

class TorrentStreamer(object):

    def __init__(self):

        self.default_params = {
            'save_path': '/code/media/',
        }

        self.session = lt.session({'listen_interfaces': '0.0.0.0:6881'})
       
        #self.session.start_dht()

        self.threaded_magnets = {}

    def get_blocking_magnet(self, magnet_, params=False, player="vlc"):

        if not params:
            params = self.default_params

        magnet = self.threaded_magnets[get_hash(magnet_)]
        print(str(magnet_))
        magnet['handle'] = lt.add_magnet_uri(self.session, str(magnet_), self.default_params)
        print(magnet['handle'].status().progress)
        has_played = False
        magnet['run'] = True
        
        print ('downloading metadata...')
        while (not magnet['handle'].has_metadata()): time.sleep(1)
        magnet['handle'].set_sequential_download(True)
        while magnet['run']:
            if magnet['share_ratio'] != -1:
                logging.debug("Streaming enabled, reordering pieces")
               # set_streaming_priorities(magnet['handle'])            

            if magnet['handle'].has_metadata():
                logging.debug("Metadata acquired")

                if has_played and stream_conditions_met(magnet):
                    logging.debug("File has been played and streamed.")
                    magnet['run'] = False
                print(magnet['handle'].status().progress)
                if not magnet['file']:
                    logging.debug("Not file yet acquired")
                    magnet['file'] = '/code/media/{}'.format(get_media_files(magnet))

                logging.debug(magnet)

                if play_conditions_met(magnet):
                    mpd = '/code/blog/{}/mpd'.format(self.namedir)
                    error = '/code/blog/{}/error'.format(self.namedir)
                    logging.debug("Launching player")
                    logging.debug("NameDir is {}".format(self.namedir))
                    logging.debug('ffmpeg -re -i "{}" -c:a aac -ac 2 -b:a 128k -c:v libx264 -pix_fmt yuv420p -profile:v baseline -preset ultrafast -tune zerolatency -vsync cfr -x264-params "nal-hrd=cbr" -b:v 500k -minrate 500k -bufsize 1000k -g 60 -f flv rtmp://rtmp:1935/dash/{}'.format(magnet['file'], get_hash(magnet_)))
                    open(mpd, 'tw').close()
                    exit_status = os.system('ffmpeg -re -i "{}" -c:a aac -ac 2 -b:a 128k -c:v libx264 -pix_fmt yuv420p -profile:v baseline -preset ultrafast -tune zerolatency -vsync cfr -x264-params "nal-hrd=cbr" -b:v 500k -minrate 500k -bufsize 1000k -g 60 -f flv rtmp://rtmp:1935/dash/{}'.format(magnet['file'], get_hash(magnet_)))
                    logging.debug("Exit status is {}".format(exit_status))
                    if exit_status > 0:
                        open(error, 'tw').close()
                    has_played = True
                else:
                    logging.debug("Not yet ready to play")



            time.sleep(5)


    def get_parallel_magnets(self, magnet_, share_ratio, play_ratio, player, namedir):

        self.namedir = namedir
        logging.info("Adding {} to download queue".format(magnet_))
        thread_ = threading.Thread(
                name="Downloading {}".format(get_hash(magnet_)),
                target=self.get_blocking_magnet,
                args=[str(magnet_)],
                kwargs={'player': player}
            )

        self.threaded_magnets[get_hash(magnet_)] = {
                'thread': thread_,
                'status': None,
                'file': None,
                'share_ratio': -1,
                'play_ratio': 5
            }

        with ThreadPoolExecutor(max_workers=4) as executor:
            for _, thread_ in self.threaded_magnets.items():
                executor.submit(thread_['thread'].run)

        return True


