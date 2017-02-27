# eloquent-media
A php trait that's provides an easy way to associate images and/or files for your laravel eloquent models.

### Installation

```
composer require ahmed-aliraqi/eloquent-media
```
### Configration
In your `config/filesystems.php` file confiure `disks.local` key to match your prefered upload path.
```php
'local' => [
    'driver' => 'local',
    'root' => public_path('uploads'),
],
```
### Usage
```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Aliraqi\Traits\HasFiles;

class Post extends Model
{
    use HasFiles;


    //...
}
```

## API

#### putFile
Method Definition:
> ```php
> /**
>  * Upload given file to this model instance.
>  *
>  * @param  string  $key
>  * @param  string  $name
>  * @param  array   $options
>  * @return string  File path
>  */
> $model->putFile($key, [$name, $options]);
> ```
> NOTE: the uploaded file will be saved in the configured path in `filesystems.php file`.

> For example `$user->putFile('avatar');` will save the uploaded file `avatar` and save it in
>`public/uploads/users/USER_ID/avatar.EXT` where USER_ID is the user id and EXT is the uploaded file extension.

> NOTE: if you use primary key other than $id it will be used automatically instead of $id.



#### file
Method Definition:
> ```php
> /**
>  * Get link of given file name that belongs to this model instance.
>  *
>  * @param  string $name
>  * @param  string $fallback
>  * @return string
>  */
> $model->file([$name, $fallback]);
> ```
> NOTE: get the link of uploaded file .
>
> For example `$user->file('avatar');` will get link of the uploaded file `avatar`
>`http://localhost:8000/uploads/users/USER_ID/avatar.ext` where USER_ID is the user id.




#### putFiles
Method Definition:
> ```php
> /**
>  * Upload given files to this model instance.
>  *
>  * @param  string  $key
>  * @param  string  $name
>  * @param  boolean  $delete
>  * @param  array  $options
>  * @return string  File path
>  */
> $model->putFiles($key, [$name, $delete, $options]);
> ```
> NOTE: the multiple uploaded files will be saved in the configured path in `filesystems.php file`.
>
>For example `$user->putFiles('avatars');` will save the uploaded file `avatars` and save it in >`public/uploads/users/USER_ID/avatars/583ac3d5a0135.ext` where USER_ID is the user id.




#### files
Method Definition:
> ```php
> /**
> * Get array of given files name that belongs to this model instance.
> *
> * @param  string $name  name of folder
> * @return Illuminate\Support\Collection
> */
> public function files($name)
> ```
> NOTE: get the link of uploaded file .
>
>For example `$user->files('avatars');` will get array collection for pathes and >links of the uploaded files `avatars`
>For example :
>```
>@foreach($user->files('avatar') as $path => $link)
>  file path : {{ $path }} <br>
>  File link : {{ $link }}
>@endforeach
>```



#### filePath
Method Definition:
> ```php
> /**
>  * Get path of given file name that belongs to this model instance.
>  *
>  * @param  string $name
>  * @return string | null
>  */
> public function filePath([$name])
> ```


#### hasGlobal
Method Definition:
> ```php
> /**
>  * Determine if the assiciated files is global or not.
>  *
>  * @param  boolean $value
>  * @return Illuminate\Database\Eloquent\Model
>  */
> public function hasGlobal([$value = true])
> ```
> For example:

> ```php
> $user->hasGlobal()->putFile('default');
> ```
> the file will save in `public/uploads/users/default.png`
> ```blade
> {{ $user-hasGlobal()->file('default') }}
> ```

#### disk
Method Definition:
> ```php
> /**
>  * Determine a filesystem instance.
>  *
>  * @param  string  $name
>  * @return Illuminate\Database\Eloquent\Model
>  */
> public function disk([$name = 'local'])
> ```
> For example:

> ```php
> $user->disk('public')->putFile('photo');
> ```
> ```blade
> {{ $user->disk('public')->file('photo') }}
> ```



### Fallback images.
If you want to return fallback image if given image not found you must create `config/fallbackimages.php` file and put the name of model table and set fallback image like this :

```php
<?php
return [
    // Get users fallback image url.
    'users' => 'http://lorempixel.com/grey/800/400/cats/Faker/',

    // Get posts fallback image url.
    'posts' => 'http://lorempixel.com/grey/800/400/cats/Faker/',
];

```
