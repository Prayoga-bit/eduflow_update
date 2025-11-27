<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploadService
{
    /**
     * The disk to use for storage.
     *
     * @var string
     */
    protected $disk = 'public';

    /**
     * The directory to store the files.
     *
     * @var string
     */
    protected $directory = 'uploads';

    /**
     * Create a new FileUploadService instance.
     *
     * @param  string  $disk
     * @param  string  $directory
     * @return void
     */
    public function __construct(string $disk = 'public', string $directory = 'uploads')
    {
        $this->disk = $disk;
        $this->directory = trim($directory, '/');
    }

    /**
     * Upload a file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $subdirectory
     * @param  array  $options
     * @return string|false
     */
    public function upload(UploadedFile $file, string $subdirectory = '', array $options = [])
    {
        try {
            $subdirectory = trim($subdirectory, '/');
            $path = $this->directory . ($subdirectory ? '/' . $subdirectory : '');
            
            // Generate a unique filename
            $filename = $this->generateFilename($file);
            
            // Process image if it's an image and options are provided
            if ($this->isImage($file) && !empty($options)) {
                return $this->processImage($file, $path, $filename, $options);
            }
            
            // Store the file
            return $file->storeAs(
                $path,
                $filename,
                ['disk' => $this->disk]
            );
        } catch (\Exception $e) {
            \Log::error('File upload failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file.
     *
     * @param  string  $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Process and store an image with the given options.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $path
     * @param  string  $filename
     * @param  array  $options
     * @return string|false
     */
    protected function processImage(UploadedFile $file, string $path, string $filename, array $options)
    {
        try {
            $image = Image::make($file);
            
            // Apply image manipulations
            if (isset($options['width']) || isset($options['height'])) {
                $width = $options['width'] ?? null;
                $height = $options['height'] ?? null;
                $aspectRatio = $options['aspect_ratio'] ?? false;
                $upsize = $options['upsize'] ?? true;
                
                $image->resize($width, $height, function ($constraint) use ($aspectRatio, $upsize) {
                    if ($aspectRatio) {
                        $constraint->aspectRatio();
                    }
                    if ($upsize) {
                        $constraint->upsize();
                    }
                });
            }
            
            // Convert to webp if requested
            if (isset($options['convert_to_webp']) && $options['convert_to_webp']) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                $image->encode('webp', $options['quality'] ?? 90);
            } else {
                $image->encode($file->getClientOriginalExtension(), $options['quality'] ?? 90);
            }
            
            // Store the image
            $fullPath = $path . '/' . $filename;
            
            if (Storage::disk($this->disk)->put($fullPath, (string) $image)) {
                return $fullPath;
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique filename for the uploaded file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);
        
        return $safeName . '-' . Str::random(10) . '.' . strtolower($extension);
    }

    /**
     * Check if the file is an image.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return bool
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Get the full URL for a stored file.
     *
     * @param  string  $path
     * @return string
     */
    public function url(string $path): string
    {
        if (empty($path)) {
            return '';
        }
        
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get the file size in a human-readable format.
     *
     * @param  string  $path
     * @return string
     */
    public function getFileSize(string $path): string
    {
        if (!$this->exists($path)) {
            return '0 KB';
        }
        
        $bytes = Storage::disk($this->disk)->size($path);
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
