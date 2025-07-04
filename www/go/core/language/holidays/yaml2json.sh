#!/bin/bash

# Specify the directory containing the YAML files
directory="/Users/michaeldehart/Projects/date-holidays/data/countries"


if [ ! -d "$directory" ]; then
    echo "Error: Directory not found!"
    exit 1
fi

# Iterate through each YAML file in the directory
for file in "$directory"/*.yaml; do
    if [ -e "$file" ]; then
        filename=$(basename "$file")
        output_file="./countries/$(echo "${filename%.yaml}.json" | tr '[:upper:]' '[:lower:]')"
        #output_file="./countries/"$(echo "${file%.yaml}.json" | tr '[:upper:]' '[:lower:]')"
        echo $output_file
        # brew install yq
        # Execute the command on each YAML file
        yq -p yaml -o json "$file" > "$output_file"
    fi
done