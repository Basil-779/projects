ffmpeg -re -i "Welcome.mp4" -c:a aac -ac 2 -b:a 128k -c:v libx264 -pix_fmt yuv420p -profile:v baseline -preset ultrafast -tune zerolatency -vsync cfr -x264-params "nal-hrd=cbr" -b:v 500k -minrate 500k -maxrate 500k -bufsize 1000k -g 60 -s 640x360 -f flv rtmp://localhost:1935/dash/welcome
#ffmpeg -re -i "Welcome.mp4" -c:v libx264 -profile:v baseline -c:a libfaac -ar 44100 -ac 2 -f flv rtmp://localhost:1935/dash/welcome