<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use App\Models\ForumPost;
use App\Models\ForumReply;

class StoreForumReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->getPost();
        
        // Check if the post is locked
        if ($post->is_locked) {
            return false;
        }
        
        // Check if the user can reply to this post
        return $post->group->is_public || $post->group->isMember($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
            'parent_id' => [
                'nullable',
                'exists:forum_replies,id',
                function ($attribute, $value, $fail) {
                    $post = $this->getPost();
                    $parentReply = ForumReply::find($value);
                    
                    if ($parentReply && $parentReply->post_id !== $post->id) {
                        $fail('The parent reply does not belong to this post.');
                    }
                    
                    // Limit nesting depth to 3 levels
                    if ($parentReply && $parentReply->depth >= 2) {
                        $fail('Maximum reply depth reached.');
                    }
                },
            ],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => [
                'file',
                'max:5120', // 5MB
                'mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt,zip,rar',
            ],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['exists:users,id'],
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
            'content.required' => 'Please provide content for your reply.',
            'content.min' => 'The reply content must be at least 2 characters.',
            'content.max' => 'The reply content may not be greater than 5000 characters.',
            'parent_id.exists' => 'The selected parent reply is invalid.',
            'attachments.max' => 'You may not upload more than 3 files.',
            'attachments.*.max' => 'Each file may not be greater than 5MB.',
            'attachments.*.mimes' => 'The file must be of type: jpeg, png, jpg, gif, pdf, doc, docx, xls, xlsx, txt, zip, or rar.',
            'mentions.*.exists' => 'One or more mentioned users do not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'content' => $this->content ? trim($this->content) : null,
            'parent_id' => $this->parent_id ? (int)$this->parent_id : null,
            'mentions' => $this->extractMentions($this->content ?? ''),
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
        unset($validated['mentions']);
        
        return $validated;
    }
    
    /**
     * Extract user mentions from the content.
     *
     * @param  string  $content
     * @return array
     */
    protected function extractMentions(string $content): array
    {
        $pattern = '/@([\w.-]+)/';
        preg_match_all($pattern, $content, $matches);
        
        if (empty($matches[1])) {
            return [];
        }
        
        // Get user IDs for the mentioned usernames
        return \App\Models\User::whereIn('username', $matches[1])
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * Get the post that this reply belongs to.
     *
     * @return \App\Models\ForumPost
     */
    protected function getPost(): \App\Models\ForumPost
    {
        return $this->route('post');
    }
}
