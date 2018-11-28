#!/bin/bash


DIR="$1"

InputFile="$(pwd)/$DIR/$DIR.svg"

OutputBaseFile="$(pwd)/$DIR/$DIR"

pngSizes=("128x128" "16x16" "192x192" "24x24" "256x256" "32x32" "48x48" "512x512" "64x64" "96x96")

for size in ${pngSizes[*]}
do
  echo Generating "$OutputBaseFile"_$size.png
  
  /usr/bin/convert -density 1536 -background none -resize $size $InputFile "$OutputBaseFile"_$size.png

done
