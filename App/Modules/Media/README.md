# Media 

## Config Options
Media Manager relies on some 3rd party command line tools to do it's job.  The first is good old fashion zip.  The config file defines the path to zip in the config file **Media/Config/Register.php** file  The default path is /usr/bin/zip  Two other commands are *ffprobe* & *ffmpeg* They are defined in the same file.


## Api Calls

### /api/Media/Repos
Get list of all current repos

### /api/Media/Repo/Add
Add a new repo, you must send a unique Name & Path

Argument | Default | Required | Explanation
--- | --- | --- | --- 
name |  | Yes | The Name for this repo, must be unique
path |  | Yes | The Path on the filesystem where this repo is found
enabled | 1 | Yes | 1 = enable, 0 = disabled


### /api/Media/Repo/Update/{id}
Update a specific repo, useful for updating paths, changing names or parking a repo disabled

Argument | Default | Required | Explanation
--- | --- | --- | --- 
name |  | Yes | The Name for this repo, must be unique
path |  | Yes | The Path on the filesystem where this repo is found
enabled | 1 | Yes | 1 = enable, 0 = disabled


### /api/Media/Repo/Delete/{id}
Delete a given repo specified by id


### /api/Media/Repo/{id}/Folders
Get a list of all the folders under a specific repoisitory.  The id is the id of the repo taken from the 

### /api/Media/Stream/{id}/{format}
Stream a specific piece of media.  The {id} will be the id of the media you wish to stream and the format is the format you want it streamed in.  

Supported formats

* mp3
* ogg
* flv
* webm
* hls


### /api/Media/Zip/{id}
The Zip option is only available for folders. It will zip the requested folder on the fly and send it to the user.

### /api/Media/Search
This is useful for searching for a specific media.  It supports pagination through the limit and offset post arguments

#### Post Arguments
Argument | Default | Required | Explanation
--- | --- | --- | --- 
limit | 50 | Yes | How many record to return at a time
offset | 0 | Yes | Used in pagination in accordance with limit so for page 2 with a limit og 50 offset would be 50, then you would get results 51-100
sortfield | name | Yes | Which field to for reford on.  Use created with sortdir = 'desc' to list by newest added
sortdir | asc | Yes | Direction sort asc (ascending) or desc (descending)
type | None | No | Filter results to a specific media type.  Available types include 'song','movie','picture','document','iso','subtitle','raw'
artist | None | No | Allows searching within id3 tags
album | None | No | Allows searching within id3 tags
genre | None | No | Allows searching within id3 tags



### /api/Media/Folder/{id}
Get the contents of a folder.  Will return all the details about a folder, what media it contains, as well as a list of any children (subdirectories) contained within this folder


### /api/Media/Folder/Art/{wxh}/{id}
Fetch the artwork for this folder.  It has a complicated set of rules for deciding the directory art work to return.  First if the directory contains mp3's then it assumes this directory is an album and so then it finds the first mp3 that has an embeded image and returns that image.  If no embeded image is found then it looks for a cover.[jpg|png|gif] or folder.[jpg|png|gif]  if that fails then it shows the first image it finds in that directory. 

The arguments in the URL are for width x height and the folder id

Argument | Default | Required | Explanation
--- | --- | --- | --- 
wxh |  | Yes | Max width and height of an image like 300x300
id |  | Yes | The ID of the 



