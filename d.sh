#!/bin/bash

input="$1"
case "${input}" in
"php")
  docker exec -it playground-xsh.php-fpm /bin/bash
  ;;
"phproot")
  docker exec -u 0 -it playground-xsh.php-fpm /bin/bash
  ;;
"db")
  docker exec -it playground-xsh.mysql /bin/bash
  ;;
*)
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  echo " ./d.sh php                                               ="
  echo " ./d.sh phproot                                           ="
  echo " ./d.sh db                                                ="
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  exit 1
  ;;
esac
