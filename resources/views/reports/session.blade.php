<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #1a1a2e;
      background: #ffffff;
      padding: 40px;
    }

    /* ── header ── */
    .header {
      border-bottom: 2px solid #4f46e5;
      padding-bottom: 16px;
      margin-bottom: 24px;
    }

    .header-office {
      font-size: 20px;
      font-weight: 700;
      color: #4f46e5;
    }

    .header-subtitle {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }

    /* ── summary ── */
    .summary {
      background: #f5f3ff;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 24px;
    }

    .summary-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #6b7280;
      margin-bottom: 10px;
    }

    .summary-grid {
      display: table;
      width: 100%;
    }

    .summary-item {
      display: table-cell;
      width: 25%;
    }

    .summary-value {
      font-size: 20px;
      font-weight: 700;
      color: #4f46e5;
    }

    .summary-label {
      font-size: 10px;
      color: #6b7280;
      margin-top: 2px;
    }

    /* ── topic ── */
    .topic {
      margin-bottom: 20px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      /*page-break-inside: avoid;*/
    }

    .topic-header {
      background: #4f46e5;
      padding: 10px 14px;
      display: table;
      width: 100%;
      page-break-inside: avoid;
      page-break-after: avoid;
    }

    .topic-label {
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
      display: table-cell;
    }

    .topic-count {
      font-size: 11px;
      color: #c7d2fe;
      display: table-cell;
      text-align: right;
      white-space: nowrap;
    }

    .topic-body {
      padding: 12px 14px;
    }

    /* ── keywords ── */
    .keywords-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #6b7280;
      margin-bottom: 6px;
    }

    .keywords-list {
      margin-bottom: 12px;
    }

    .keyword {
      display: inline-block;
      background: #ede9fe;
      color: #4f46e5;
      font-size: 10px;
      padding: 2px 8px;
      border-radius: 99px;
      margin-right: 4px;
      margin-bottom: 4px;
    }

    /* ── feedbacks ── */
    .feedbacks-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #6b7280;
      margin-bottom: 6px;
    }

    .feedback-item {
      display: table;
      width: 100%;
      padding: 6px 0;
      border-bottom: 1px solid #f3f4f6;
      page-break-inside: avoid;
    }

    .feedback-item:last-child {
      border-bottom: none;
    }

    .feedback-text {
      display: table-cell;
      font-size: 11px;
      color: #374151;
      padding-right: 8px;
    }

    .feedback-score {
      display: table-cell;
      font-size: 10px;
      color: #9ca3af;
      text-align: right;
      white-space: nowrap;
      width: 60px;
    }

    /* ── footer ── */
    .footer {
      margin-top: 32px;
      padding-top: 12px;
      border-top: 1px solid #e5e7eb;
      font-size: 10px;
      color: #9ca3af;
      text-align: center;
    }
  </style>
</head>
<body>

  {{-- header --}}
  <div class="header">
    <div class="header-office">{{ $report['office'] }}</div>
    <div class="header-subtitle">
      Analysis Session Report &nbsp;·&nbsp; Generated {{ now()->format('F j, Y') }}
    </div>
  </div>

  {{-- summary --}}
  <div class="summary">
    <div class="summary-title">Summary</div>
    <div class="summary-grid">
      <div class="summary-item">
        <div class="summary-value">{{ $report['feedback_count'] }}</div>
        <div class="summary-label">Feedbacks Analyzed</div>
      </div>
      <div class="summary-item">
        <div class="summary-value">{{ $report['topic_count'] }}</div>
        <div class="summary-label">Topics Identified</div>
      </div>
      <div class="summary-item">
        <div class="summary-value">
          {{ $report['started_at'] ? \Carbon\Carbon::parse($report['started_at'])->format('M j, Y') : '—' }}
        </div>
        <div class="summary-label">Started</div>
      </div>
      <div class="summary-item">
        <div class="summary-value">
          {{ $report['completed_at'] ? \Carbon\Carbon::parse($report['completed_at'])->format('M j, Y') : '—' }}
        </div>
        <div class="summary-label">Completed</div>
      </div>
    </div>
  </div>

  {{-- topics --}}
  @foreach ($report['topics'] as $i => $topic)
    <div class="topic">
      <div class="topic-header">
        <div class="topic-label">{{ $i + 1 }}. {{ $topic['label'] }}</div>
        <div class="topic-count">{{ $topic['feedback_count'] }} feedback{{ $topic['feedback_count'] !== 1 ? 's' : '' }}</div>
      </div>
      <div class="topic-body">

        {{-- keywords --}}
        @if (!empty($topic['keywords']))
          <div class="keywords-label">Keywords</div>
          <div class="keywords-list">
            @foreach ($topic['keywords'] as $keyword)
              <span class="keyword">{{ $keyword }}</span>
            @endforeach
          </div>
        @endif

        {{-- sample feedbacks --}}
        @if (!empty($topic['sample_feedbacks']))
          <div class="feedbacks-label">Feedbacks</div>
          @foreach ($topic['sample_feedbacks'] as $feedback)
            <div class="feedback-item">
              <div class="feedback-text">{{ $feedback['text'] ?? $feedback['cleaned_text'] ?? '—' }}</div>
            </div>
          @endforeach
        @endif

      </div>
    </div>
  @endforeach

  {{-- footer --}}
  <div class="footer">
    {{ $report['office'] }} &nbsp;·&nbsp; {{ $report['topic_count'] }} topics &nbsp;·&nbsp; {{ $report['feedback_count'] }} feedbacks analyzed
  </div>

</body>
</html>
