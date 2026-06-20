<x-layouts.form :office="$office" title="Feedback Submitted">
    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="flex flex-col text-text">
                <h1 class="text-2xl font-medium mb-2">Thank you!</h1>
                <p class="text-sm">
                    Your feedback has been submitted to
                    <span class="font-medium">{{ $office->office_name }}</span>.
                </p>
            </div>
            <x-slot:footer>
                <a
                    href="{{ route('student.feedback.show', $office->access_link) }}"
                    class="text-sm underline"
                    style="color: var(--office-color)"
                >
                    Submit another response
                </a>
            </x-slot:footer>
        </x-card>
    </div>
</x-layouts.form>
