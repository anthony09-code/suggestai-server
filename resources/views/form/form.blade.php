<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $office->office_name }} — Submit Feedback</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div>

        {{-- Header --}}
        <div>
            <div>
                <h1>{{ $office->office_name }}</h1>
                @if($office->description)
                    <p>{{ $office->description }}</p>
                @endif
            </div>
            <div>
                <p>
                    Logged in as <span>{{ auth('student')->user()->email }}</span>
                </p>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <p>{{ session('success') }}</p>
        @endif

        {{-- Error Messages --}}
        @if($errors->any())
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- Feedback Form --}}
        <form action="{{ route('student.feedback.store', $office->access_link) }}" method="POST">
            @csrf

            {{-- Auto detect submission method --}}
            <input
                type="hidden"
                name="submission_method"
                value="{{ request()->query('via') === 'qr' ? 'qr_code' : 'manual_pick' }}"
            >

            {{-- Feedback Text --}}
            <div>
                <label for="raw_text">Your Feedback</label>
                <textarea
                    id="raw_text"
                    name="raw_text"
                    rows="5"
                    placeholder="Write your feedback here..."
                >{{ old('raw_text') }}</textarea>
                @error('raw_text')
                    <p>{{ $message }}</p>
                @enderror
            </div>


            {{-- Submit --}}
            <div>
                <button type="submit">Submit Feedback</button>
            </div>

        </form>

    </div>
</body>
</html>
