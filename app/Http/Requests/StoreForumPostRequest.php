<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use App\Models\ForumGroup;
use App\Models\ForumPost;

class StoreForumPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user can post in this group
        $group = ForumGroup::findOrFail($this->route('group'));
        return $group->is_public || $group->isMember(auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $post = $this->route('post');
        $uniqueTitleRule = Rule::unique('forum_posts', 'title')
            ->where('forum_group_id', $this->route('group'))
            ->ignore($post?->id);

        return [
            'title' => [
                'required',
                'string',
                'max:200',
                $uniqueTitleRule,
                'not_regex:/[^\pL\pM\pN\s\-_,.!;:()\[\]{}@#%&*+=\/\\|]/u',
            ],
            'content' => ['required', 'string', 'min:10', 'max:10000'],
            'is_pinned' => ['sometimes', 'boolean'],
            'is_locked' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip,rar',
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
            'title.required' => 'The post title is required.',
            'title.max' => 'The post title may not be greater than 200 characters.',
            'title.unique' => 'A post with this title already exists in this group.',
            'title.not_regex' => 'The post title contains invalid characters.',
            'content.required' => 'Please provide content for your post.',
            'content.min' => 'The post content must be at least 10 characters.',
            'content.max' => 'The post content may not be greater than 10000 characters.',
            'attachments.max' => 'You may not upload more than 5 files.',
            'attachments.*.max' => 'Each file may not be greater than 10MB.',
            'attachments.*.mimes' => 'The file must be of type: jpeg, png, jpg, gif, pdf, doc, docx, xls, xlsx, txt, zip, or rar.',
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
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'content' => $this->content ? trim($this->content) : null,
            'is_pinned' => $this->boolean('is_pinned'),
            'is_locked' => $this->boolean('is_locked'),
            'tags' => $this->tags ? array_map('strtolower', (array)$this->tags) : [],
        ]);
    }

    /**
     * Get the validated data from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();
        
        // Remove the attachments from the validated data as we'll handle them separately
        unset($validated['attachments']);
        
        return $validated;
    }
}
