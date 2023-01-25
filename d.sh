#!/bin/bash

input="$1"
case "${input}" in
"php")
  docker exec -it xmlship.php-fpm /bin/bash
  ;;
"phproot")
  docker exec -u 0 -it xmlship.php-fpm /bin/bash
  ;;
"db")
  docker exec -it xmlship.mysql /bin/bash
  ;;
*)
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  echo " ./hooks.sh php                                               ="
  echo " ./hooks.sh db                                                ="
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  exit 1
  ;;
esac
