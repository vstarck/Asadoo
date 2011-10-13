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

digested = "<?php\nnamespace asadoo;\nuse Closure;\nuse Exception;"
files_names.each do |file_name|
    content = IO.read "src/#{file_name}"

    content.gsub!(/<\?php/, '')
    content.gsub!(/^namespace[\sa-z\d]+;/i, '')
    content.gsub!(/^use[^;]+;/, '')

    digested += content
    digested += "\n"
end

filename = 'dist/index.php'
File.open(filename, "w") { |file| file.write(digested) }
puts "Created #{filename}"