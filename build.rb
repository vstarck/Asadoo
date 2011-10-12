#!/usr/bin/env ruby

files_names = [
    'core/Asadoo.php',
    'core/IHandler.php',
    'core/Request.php',
    'core/Response.php',
    'dependences/Config.php',
    'dependences/FileCache.php',
    'dependences/Logger.php',
    'handlers/AbstractFileHandler.php',
    'handlers/GenericCSSHandler.php',
    'handlers/GenericJSHandler.php',
    'handlers/GenericPostHandler.php',
    'init.php'
]

digested = '<?php'
files_names.each do |file_name|
  digested += (IO.read "src/#{file_name}").gsub /<\?php/, ''
  digested += "\n\n"
end

filename = 'dist/index.php'
File.open(filename, "w") { |file| file.write(digested) }
puts "Created #{filename}"