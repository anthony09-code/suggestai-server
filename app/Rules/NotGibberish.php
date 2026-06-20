<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NotGibberish implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        $value = trim($value);

        if (str_word_count($value) < 3) {
            $fail("Please write at least 3 meaningful words in your feedback.");
            return;
        }

        $words = preg_split("/\s+/", $value);
        $gibberishCount = 0;

        foreach ($words as $word) {
            $word = preg_replace("/[^a-zA-Z]/", "", $word);
            if (empty($word)) {
                continue;
            }

            if (preg_match('/(.)\1{3,}/', $word)) {
                $gibberishCount++;
                continue;
            }

            $vowels = preg_match_all("/[aeiouAEIOU]/", $word);
            $length = strlen($word);
            if ($length > 3 && $vowels === 0) {
                $gibberishCount++;
                continue;
            }

            $uniqueChars = count(array_unique(str_split(strtolower($word))));
            if ($length > 4 && $uniqueChars / $length > 0.85) {
                $gibberishCount++;
            }
        }

        $totalWords = count(
            array_filter(
                $words,
                fn($w) => !empty(preg_replace("/[^a-zA-Z]/", "", $w)),
            ),
        );
        if ($totalWords > 0 && $gibberishCount / $totalWords > 0.5) {
            $fail(
                "Your feedback appears to contain gibberish. Please write meaningful feedback.",
            );
        }
    }
}
