@php $required = $question['required'] ?? false; @endphp
<div class="card mb-3">
    <div class="card-body">
        <p class="fw-medium">{{ $index }}. {{ $question['text'] }} @if($required)<span class="text-danger">*</span>@endif</p>

        @if(($question['type'] ?? 'single_select') === 'text')
            <textarea name="responses[{{ $question['id'] }}]" class="form-control" rows="2" @if($required) required @endif @disabled($disabled)></textarea>
        @elseif(($question['type'] ?? 'single_select') === 'likert')
            @foreach(\App\Models\WorkshopSurveyTemplate::LIKERT_SCALE as $value => $label)
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="responses[{{ $question['id'] }}]" value="{{ $value }}" id="{{ $question['id'] }}-{{ $value }}" @if($required) required @endif @disabled($disabled)>
                    <label class="form-check-label" for="{{ $question['id'] }}-{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
        @elseif(($question['type'] ?? 'single_select') === 'multi_select')
            @foreach($question['options'] ?? [] as $option)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="responses[{{ $question['id'] }}][]" value="{{ $option }}" id="{{ $question['id'] }}-{{ $loop->index }}" @disabled($disabled)>
                    <label class="form-check-label" for="{{ $question['id'] }}-{{ $loop->index }}">{{ $option }}</label>
                </div>
            @endforeach
        @else
            @foreach($question['options'] ?? [] as $option)
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="responses[{{ $question['id'] }}]" value="{{ $option }}" id="{{ $question['id'] }}-{{ $loop->index }}" @if($required) required @endif @disabled($disabled)>
                    <label class="form-check-label" for="{{ $question['id'] }}-{{ $loop->index }}">{{ $option }}</label>
                </div>
            @endforeach
        @endif
    </div>
</div>
