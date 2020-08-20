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

BUFF_PERCENT = 5
logging.basicConfig(
    level=logging.DEBUG,
    format='[%(levelname)s] (%(threadName)-10s) %(message)s'
)

def get_hash(magnet_):
    """
        return readable hash
    """
    magnet_p = parse_qs(urlparse(magnet_).query)
    return re.match('urn:btih:(.*)', magnet_p['xt'][0]).group(1)


def get_media_files(magnet):
    """
        Get one only media file
    """
    def has_reserved_word(file_):
        """
            Check if has reserved words
        """
        reserved_words = ['sample']
        for reserved in reserved_words:
            if reserved in file_:
                return True
        return False

    def is_video(file_):
        """
            Check if is video in the mimetype
        """
        mime = mimetypes.guess_type(file_)
        if mime[0] and 'video' in mime[0]:
            return True
        return False

    def get_media_file(files):
        """
            Return files.
        """
        first_pass = [fil for fil in files if is_video(fil)]
        return filter(lambda x: not has_reserved_word(x), first_pass)[0]

    print("here")
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
    """
        Returns a readable status
    """
    status = magnet['handle'].status()
    if not status:
        return "None"
    return '%.2f%% (d: %.1f kb/s up: %.1f kB/s p: %d)\r' % (
        status.progress * 100, status.download_rate / 1000,
        status.upload_rate / 1000,
        status.num_peers
    )


def make_download_status(magnet):
    """
        Make a queue readable.
    """

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
    """
        Set priorities for chunk
    """
    #handle.set_sequential_download(True)
    
    pieces = dict(enumerate(handle.status().pieces))
    next_pieces = [key for key, val in pieces.iteritems() if val][:3]
    for piece in next_pieces:
        handle.piece_priority(piece, 7)


def is_playable(file_, handle):
    """
        Check if we've got 1/5th of the file
    """
    if not file_:
        return False
    status_ = make_status_readable(handle.status())
    return status_.progress > BUFF_PERCENT  # Wait until we have 1/5

def stream_conditions_met(magnet):
    """
        Returns False if -1 has been passed as
        stream_ratio, True if 0.

        Otherwise, we'll have to implement a seed ratio watcher.
    """
    if magnet['stream_ratio'] == -1:
        return False
    elif magnet['stream_ratio'] == 0:
        return True
    else:
        # TODO, seed ratio
        raise NotImplementedError()

def play_conditions_met(magnet):
    """
        Returns true if file has been found and
        torrent download ratio excees play ratio specified.
    """
    if not magnet['file']:
        return False

    if magnet['play_ratio'] == -1:
        return False

    return magnet['play_ratio'] <= magnet['handle'].status().progress * 100

class TorrentStreamer(object):
    """
        Torrent Streaming service
    """
    def __init__(self):
        """
            Start session listening on default ports 6881, 6891
            Holds common session magnets between threads with
            the following format and defaults:

            ::

                self.threaded_magnets[magnet_hash] = {
                    'thread': thread_,
                    'status': None,
                    'file': None,
                    'share_ratio': 0,
                    'play_ratio': 5
                }

        """
        self.default_params = {
            'save_path': '/home/kostya/Desktop/',
        }

        self.session = lt.session({'listen_interfaces': '0.0.0.0:6881'})
       
        #self.session.start_dht()

        self.threaded_magnets = {}

    def get_blocking_magnet(self, magnet_, params=False, player="vlc"):
        """
            Start downloading a magnet link

            :param dict magnet_: magnet
            :param dict params: Params to pass to libtorrent's add_magnet_uri
            :param string player: Player (defaults to mplayer)
        """

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
                logging.debug("Metadata adquired")

                if has_played and stream_conditions_met(magnet):
                    logging.debug("File has been played and streamed.")
                    magnet['run'] = False
                print(magnet['handle'].status().progress)
                if not magnet['file']:
                    logging.debug("Not file yet adquired")
                    magnet['file'] = '/home/kostya/Desktop/{}'.format(get_media_files(magnet))


                

                logging.debug(magnet)

                if play_conditions_met(magnet):
                    logging.debug("Launching player")
                    print(magnet['file'])
                    subprocess.call(["vlc", magnet['file']])
                    has_played = True
                else:
                    logging.debug("Not yet ready to play")



            time.sleep(5)


    def get_parallel_magnets(self, magnet_, share_ratio, play_ratio, player):
        """
            Parallelize magnet downloading.

            :param list magnets: list of magnets to download.
            :param int share_ratio: Seed ratio before finishing. If -1 no seed.
            :param int play_ratio: Download ratio before start playing.
                                   If -1 don't play. If 0 play once finished.
            :param str player: Player
        """
        
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
                'share_ratio': 0,
                'play_ratio': 5
            }

        with ThreadPoolExecutor(max_workers=4) as executor:
            for _, thread_ in self.threaded_magnets.items():
                executor.submit(thread_['thread'].run)

        return True


def main():
    """
        Play a torrent.
    """
    parser = argparse.ArgumentParser("stream_torrent")
    parser.add_argument('magnet', metavar='magnet', type=str, nargs='+',
                        help='Magnet link to stream')
    args = parser.parse_args()
    print(args.magnet[1])
    TorrentStreamer().get_parallel_magnets("magnet:?xt=urn:btih:9e0ee0f59274bc1088c63a6a9e88dcab56ce741b&dn=Rick.and.Morty.S04E05.1080p.WEBRip.x264-TBS%5BTGx%5D&tr=udp%3A%2F%2Ftracker.openbittorrent.com%3A80&tr=udp%3A%2F%2Ftracker.publicbt.com%3A80&tr=udp%3A%2F%2Ftracker.ccc.de%3A80", -1, 5, "vlc")

if __name__ == "__main__":
    main()
