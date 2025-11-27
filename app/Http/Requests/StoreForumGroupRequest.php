<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use App\Models\ForumGroup;

class StoreForumGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('forum_groups', 'name')->ignore($this->route('group')),
                'not_regex:/[^\pL\pM\pN\s\-_,.!;:()\[\]{}@#%&*+=\/\\|]/u',
            ],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'is_public' => ['boolean'],
            'banner_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                File::image()
                    ->min(120, 120) // Min dimensions 120x120
                    ->max(4000, 4000) // Max dimensions 4000x4000
                    ->ratio(4 / 1), // 4:1 aspect ratio
            ],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'max:20', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The group name is required.',
            'name.max' => 'The group name may not be greater than 100 characters.',
            'name.unique' => 'A group with this name already exists.',
            'name.not_regex' => 'The group name contains invalid characters. Only letters, numbers, spaces, and basic punctuation are allowed.',
            'description.required' => 'Please provide a description for the group.',
            'description.min' => 'The description must be at least 10 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'banner_image.image' => 'The banner must be an image file.',
            'banner_image.mimes' => 'The banner must be a file of type: jpeg, png, jpg, gif, or webp.',
            'banner_image.max' => 'The banner may not be greater than 5MB.',
            'banner_image.dimensions' => 'The banner has invalid dimensions. It must be between 120x120 and 4000x4000 pixels with a 4:1 aspect ratio.',
            'tags.max' => 'You may not specify more than 5 tags.',
            'tags.*.max' => 'Each tag may not be greater than 20 characters.',
            'tags.*.regex' => 'Tags may only contain letters, numbers, and hyphens, and must be lowercase.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('description')) {
            $this->merge([
                'description' => trim($this->description),
            ]);
        }

        if ($this->has('tags')) {
            $this->merge([
                'tags' => array_map('strtolower', $this->tags),
            ]);
        }
    }
}
