FROM nginx:1.17.9-alpine

COPY config/ /
ADD entrypoint.sh /entrypoint.sh

ENTRYPOINT /bin/sh -c "/bin/sh /entrypoint.sh"