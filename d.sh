#!/bin/bash

input="$1"
case "${input}" in
"php")
  docker exec -it project-fl1.php-fpm /bin/bash
  ;;
"phproot")
  docker exec -u 0 -it project-fl1.php-fpm /bin/bash
  ;;
"db")
  docker exec -it project-fl1.mysql /bin/bash
  ;;
*)
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  echo " ./hooks.sh php                                               ="
  echo " ./hooks.sh phproot                                           ="
  echo " ./hooks.sh db                                                ="
  echo "= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
  exit 1
  ;;
esac
