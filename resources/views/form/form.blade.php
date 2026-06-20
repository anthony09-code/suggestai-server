<x-layouts.form :office="$office" title="{{ $office->office_name }} — Submit Feedback">
    <div class="max-w-2xl mx-auto">
        <div class="space-y-2">
            {{-- Header Image --}}
            @if($office->image_url)
                <div class="mb-4">
                    <img
                        src="{{ $office->image_url }}"
                        alt="{{ $office->office_name }}"
                        class="w-full h-48 object-cover rounded-lg"
                    />
                </div>
            @endif

            {{-- Office Info --}}
            <x-card
                title="{{ $office->office_name }}"
                description="{{ $office->description ?? 'No description provided.' }}"
            >
                <x-slot:footer>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-text-muted">Logged in as:
                            <span class="italic text-text">{{ auth('student')->user()->email }}</span>
                        </span>
                        <!--<form action="{{ route('student.logout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="office" value="{{ $office->access_link }}">
                            <button
                                type="submit"
                                class="underline cursor-pointer"
                                style="color: var(--office-color)"
                            >
                                Switch account
                            </button>
                        </form>-->
                    </div>
                </x-slot:footer>
            </x-card>

            {{-- Feedback Form --}}
            <form action="{{ route('student.feedback.store', $office->access_link) }}" method="POST" class="space-y-2">
                @csrf

                {{-- Feedback Text --}}
                <x-card :accent="false">
                    <label for="raw_text" class="block text-sm font-medium mb-1.5">
                        Your Feedback <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="raw_text"
                        name="raw_text"
                        rows="5"
                        placeholder="Write your feedback here..."
                        class="w-full border-b border-border py-3 text-sm placeholder-text-muted outline-none resize-none transition-colors"
                        onfocus="this.style.borderColor='var(--office-color)'"
                        onblur="this.style.borderColor=''"
                    >{{ old('raw_text') }}</textarea>
                    <div class="flex items-center justify-between mt-2">
                        <div>
                            @error('raw_text')
                                <p class="text-red-500 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <span id="char-count" class="text-xs text-text-muted ml-auto">0 / 250</span>
                    </div>
                </x-card>

                {{-- Anonymous --}}
                <x-card :accent="false">
                    <div class="flex items-center gap-4">
                        <input type="hidden" name="is_anonymous" value="0" />
                        <input
                            type="checkbox"
                            name="is_anonymous"
                            id="is_anonymous"
                            value="1"
                            class="w-4 h-4 cursor-pointer"
                            style="accent-color: var(--office-color)"

                        />
                        <div>
                            <p class="text-sm font-medium">Submit Anonymously</p>
                            <p class="text-sm text-text-muted">Your name will not be attached to this feedback</p>
                        </div>
                    </div>
                </x-card>

                {{-- Submit --}}
                <div class="flex items-center justify-between">
                    <x-button type="submit" variant="primary" id="submit-btn">Submit</x-button>
                    <x-button type="reset" variant="ghost">Clear Form</x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.form>
