<?php
namespace Aliraqi\Traits;
use Storage;
/**
 * Upload and get files
 */
trait HasFiles
{
  /*
  |--------------------------------------------------------------------------
  | Filesystem Disks
  |--------------------------------------------------------------------------
  |
  | Here you may configure as many filesystem "disks" as you wish, and you
  | may even configure multiple disks of the same driver. Defaults have
  | been setup for each driver as an example of the required options.
  |
  | Supported Drivers: "local", "ftp", "s3", "rackspace"
  |
  */
  private $disk = 'public';

  /**
   * Get link of given file name that belongs to this model instance.
   *
   * @param  string $name
   * @param  string $fallback
   * @return string
   */
  public function file($name = null, $fallback = null)
  {
    // Set fallback url by table name if null.
    $url = is_null($fallback) ? $this->getFallback() : $fallback;

    // Get full path of file.
    $fullPath = $this->getFullPath();

    // Get file extention.
    $filesMatch = collect(glob($fullPath.$name.'.*'));
    // Check if file exists.
    if (count($filesMatch) > 0)
    {
      $file = class_basename($filesMatch->first());
      // Get file http url.
      $url  = url(Storage::url($this->makeStoragePath().$file));

      $url  = str_replace(DIRECTORY_SEPARATOR, '/', $url);
    }

    return $url;
  }

  /**
   * Get path of given file name that belongs to this model instance.
   *
   * @param  string $name
   * @return string | null
   */
  public function filePath($name = null)
  {

    // Get full path of file.
    $fullPath = $this->getFullPath();

    // Get file extention.
    $filesMatch = collect(glob($fullPath.$name.'.*'));

    // Check if file exists.
    if (count($filesMatch) > 0)
    {
      // Get file basename.
      $file = class_basename($filesMatch->first());

      if (Storage::disk($this->disk)->exists($this->makeStoragePath().$file)) {

        return base_path($this->makeStoragePath().$file);
      }

    }

    return null;
  }

  /**
   * Get array of given files name that belongs to this model instance.
   *
   * @param  string $name  name of folder
   * @return Illuminate\Support\Collection
   */
  public function files($name)
  {
    // Get full path of files.
    $fullPath =$this->getFullPath().$name.DIRECTORY_SEPARATOR;

    // Get files array.
    $filesMatch = glob($fullPath.'*.*');
    // Check if files exists.
    $urls = collect([]);
    if (count($filesMatch) > 0)
    {
      foreach ($filesMatch as $filePath) {
        // Get file basename.
        $file = class_basename($filePath);

        $url  = url(Storage::url($this->makeStoragePath().$name.'/'.$file));

        $url  = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        // Get file http url with delete path.  [$pathToDelete => $fileUrl]
        $urls[$this->makeStoragePath().$name.DIRECTORY_SEPARATOR.$file] = $url;
      }
    }

    return collect($urls);
  }

  /**
   * Upload given file to this model instance.
   *
   * @param  string  $key
   * @param  string  $name
   * @param  array   $options
   * @return string  File path
   */
  public function putFile($key, $name = null, $options = [])
  {
    // Path of given file.
    $path = $this->makeStoragePath();

    // Set file basename.
    $name = is_null($name) ? $key : $name;

    // Get full path of file.
    $fullPath = $this->getFullPath();

    // Get file extention.
    $filesMatch = glob($fullPath.$name.'.*');
    // Upload the new file.
    if (request()->hasFile($key))
    {
      // Check if files exists.
      if (count($filesMatch) > 0)
      {
        // List of old files.
        foreach ($filesMatch as $oldPath)
        {
          // Get files basename to delete.
          $file = class_basename($oldPath);
          // Delete file.
          Storage::disk($this->disk)->delete($path.$file);
        }
      }
      // Get file extension.
      $extension = request()->file($key)->extension();
      $name = $name.'.'.$extension;

      $disk = isset($options['disk']) ? $options['disk'] : $this->disk;

      $options = array_merge($options, ['disk' => $disk]);

      return request()->file($key)->storeAs($path, $name, $options);
    }
  }


  /**
   * Upload given file to this model instance.
   *
   * @param  string  $key
   * @param  string  $name
   * @param  boolean  $delete
   * @param  array  $options
   * @return string  File path
   */
  public function putFiles($key, $name = null, $delete = false, $options = [])
  {
    // Set file basename.
    $name = is_null($name) ? $key : $name;

    // Path of given file.
    $path = $this->makeStoragePath().$name;


    // Get full path of file.
    $fullPath = $this->getFullPath().$name.DIRECTORY_SEPARATOR;
    // Get file extention.
    $filesMatch = glob($fullPath.'*.*');

    // Upload the new file.
    if (is_array(request()->file($key)))
    {
      foreach (request()->file($key) as $requestFile)
      {

          if ($delete)
          {
            // Check if files exists.
            if (count($filesMatch) > 0)
            {
              // List of old files.
              foreach ($filesMatch as $oldPath)
              {
                // Get files basename to delete.
                $file = class_basename($oldPath);
                // Delete file.
                Storage::disk($this->disk)->delete($path.'/'.$file);
              }
            }
          }
          // Get file extension.
          $extension = $requestFile->extension();

          $name = uniqid().'.'.$extension;

          $disk = isset($options['disk']) ? $options['disk'] : $this->disk;

          $options = array_merge($options, ['disk' => $disk]);

          $requestFile->storeAs($path, $name, $options);

      }
    }
  }

  /**
   * Get upload path.
   *
   * @return string
   */
  public function makeStoragePath()
  {
      if ($this->is_global)
      {
        return $this->getTable().DIRECTORY_SEPARATOR;
      }
      return $this->getTable().DIRECTORY_SEPARATOR.$this->getKey().DIRECTORY_SEPARATOR;
  }

  /**
   * Get full upload path.
   *
   * @return string
   */
  public function getFullPath()
  {
      if ($this->is_global)
      {
        return config('filesystems.disks.'.$this->disk.'.root').
             DIRECTORY_SEPARATOR.$this->getTable().DIRECTORY_SEPARATOR;
      }
      return config('filesystems.disks.'.$this->disk.'.root').
             DIRECTORY_SEPARATOR.$this->getTable().
             DIRECTORY_SEPARATOR.$this->getKey().DIRECTORY_SEPARATOR;
  }

  /**
   * Get fallback image.
   *
   * @return string
   */
  public function getFallback()
  {
      return config('fallbackimages.'.$this->getTable());
  }


    /**
     * Determine if the assiciated files is global or not.
     *
     * @param  boolean $value
     * @return Illuminate\Database\Eloquent\Model
     */
    public function hasGlobal($value = true)
    {
      $this->is_global = $value;

      return $this;
    }

    /**
     * Determine a filesystem instance.
     *
     * @param  string  $name
     * @return Illuminate\Database\Eloquent\Model
     */
    public function disk($name = 'local')
    {
        $this->disk = $name;

        return $this;
    }

}
