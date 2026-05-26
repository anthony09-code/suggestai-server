<?php
namespace App\Exports;

use App\Filters\FeedbackFilter;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Builder;

class FeedbackExport extends BaseExport
{
    public function __construct(
        private ?string $officeId = null,
        private array $filters = [],
    ) {}

    public function query(): Builder
    {
        $query = Feedback::query()
            ->with(["student", "office"])
            ->when(
                $this->officeId,
                fn($q) => $q->where("office_id", $this->officeId),
            );

        return FeedbackFilter::apply($query, $this->filters);
    }

    public function headings(): array
    {
        return [
            "ID",
            "Student",
            "Email",
            "Office",
            "Feedback",
            "Status",
            "Anonymous",
            "Submitted At",
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->is_anonymous ? "Anonymous" : $row->student?->name ?? "—",
            $row->is_anonymous ? "—" : $row->student?->email ?? "—",
            $row->office?->office_name ?? "—",
            $row->raw_text ?? "—",
            ucfirst($row->status),
            $row->is_anonymous ? "Yes" : "No",
            $row->created_at?->format("Y-m-d H:i"),
        ];
    }
}
