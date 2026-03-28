<flux:modal name="website-modal" class="md:w-96">
    <form wire:submit="saveWebsite" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ !$isEditMode ? 'Create a new website' : 'Edit webste ' . $name }}
            </flux:heading>
            <flux:text class="mt-2">{{ !$isEditMode ? 'Enter' : 'Edit' }} website details</flux:text>
        </div>

        <div class="form-group">
            <flux:input wire:model="name" label="Name" placeholder="Website name" />
        </div>

        <div class="form-group">
            <flux:input wire:model="url" label="URL" placeholder="Website URL" />
        </div>

        @if ($isEditMode)
            <div class="form-group">
                <flux:input wire:model="meta_title" label="Meta Title" placeholder="Meta title" />
            </div>

            <div class="form-group">
                <flux:input wire:model="meta_description" label="Meta Description" placeholder="Meta description" />
            </div>

            <div class="form-group">
                <flux:input wire:model="meta_image_url" label="Meta Image URL" placeholder="Meta image url" />
            </div>
        @endif

        <div class="flex">
            <flux:spacer />

            <flux:button type="submit" variant="primary" class="cursor-pointer">Save changes</flux:button>
        </div>
    </form>
</flux:modal>
