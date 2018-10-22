prefix="/var/lib/mysql/homeserver/"
suffix=".MYI"

for filename in /var/lib/mysql/homeserver/*.MYI; do
  filename = ${$filename#$prefix}
  filename = ${$filename%$suffix}
  param=$param$leer$filename
done
echo $param  
