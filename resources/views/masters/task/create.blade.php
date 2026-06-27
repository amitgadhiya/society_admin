@extends('layouts.app')

@section('title', 'Add Task — SocietyMS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('task.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Task</li>
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('task.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold text-dark mb-0">Add Task</h4>
        <p class="text-muted small mb-0">Create a new watchman task template</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <form action="{{ route('task.store') }}" method="POST">
            @csrf

            {{-- Basic Info --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold py-3">Task Details</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}"
                                   placeholder="e.g. Morning Gate Check"
                                   class="form-control @error('title') is-invalid @enderror">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      placeholder="Optional task details or instructions…"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-4">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active"   {{ old('status','active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Task Recurrence --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold py-3">Task Recurrence</div>
                <div class="card-body p-4">

                    <div class="form-check mb-4">
                        <input type="hidden" name="is_repetitive" value="0">
                        <input type="checkbox" id="is_repetitive" name="is_repetitive" value="1"
                               class="form-check-input"
                               {{ old('is_repetitive') ? 'checked' : '' }}
                               onchange="toggleRecurrence(this.checked)">
                        <label class="form-check-label fw-semibold" for="is_repetitive">
                            This is a repetitive task
                        </label>
                    </div>

                    {{-- Non-repetitive: single deadline --}}
                    <div id="section_deadline" {{ old('is_repetitive') ? 'style="display:none"' : '' }}>
                        <label for="deadline_date" class="form-label fw-semibold">
                            Deadline Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="deadline_date" name="deadline_date"
                               value="{{ old('deadline_date') }}"
                               class="form-control @error('deadline_date') is-invalid @enderror"
                               style="max-width:260px">
                        @error('deadline_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Repetitive section --}}
                    <div id="section_repetitive" {{ old('is_repetitive') ? '' : 'style="display:none"' }}>

                        <div class="row g-3 mb-3">

                            {{-- Days to complete --}}
                            <div class="col-md-4">
                                <label for="days_to_complete" class="form-label fw-semibold">
                                    Days to Complete <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="days_to_complete" name="days_to_complete"
                                       value="{{ old('days_to_complete') }}" min="1"
                                       placeholder="e.g., 5 days from start"
                                       class="form-control @error('days_to_complete') is-invalid @enderror">
                                <div class="form-text">Task must be completed within this many days from the start date</div>
                                @error('days_to_complete')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Recurrence Type --}}
                            <div class="col-md-4">
                                <label for="recurrence_type" class="form-label fw-semibold">
                                    Recurrence Type <span class="text-danger">*</span>
                                </label>
                                <select id="recurrence_type" name="recurrence_type"
                                        class="form-select @error('recurrence_type') is-invalid @enderror"
                                        onchange="syncType()">
                                    <option value="">Select recurrence type</option>
                                    <option value="daily"     {{ old('recurrence_type') === 'daily'     ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly"    {{ old('recurrence_type') === 'weekly'    ? 'selected' : '' }}>Weekly (Specific Days)</option>
                                    <option value="monthly"   {{ old('recurrence_type') === 'monthly'   ? 'selected' : '' }}>Monthly (Specific Date)</option>
                                    <option value="quarterly" {{ old('recurrence_type') === 'quarterly' ? 'selected' : '' }}>Quarterly (Every 3 Months)</option>
                                    <option value="biannual"  {{ old('recurrence_type') === 'biannual'  ? 'selected' : '' }}>Biannual (Every 6 Months)</option>
                                    <option value="annual"    {{ old('recurrence_type') === 'annual'    ? 'selected' : '' }}>Annual (Every 12 Months)</option>
                                </select>
                                @error('recurrence_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Recurrence Ends --}}
                            <div class="col-md-4">
                                <label for="recurrence_ends" class="form-label fw-semibold">
                                    Recurrence Ends <span class="text-danger">*</span>
                                </label>
                                <select id="recurrence_ends" name="recurrence_ends"
                                        class="form-select @error('recurrence_ends') is-invalid @enderror"
                                        onchange="syncEnds()">
                                    <option value="">Select end type</option>
                                    <option value="never"             {{ old('recurrence_ends') === 'never'             ? 'selected' : '' }}>Never Ends</option>
                                    <option value="after_occurrences" {{ old('recurrence_ends') === 'after_occurrences' ? 'selected' : '' }}>After X Occurrences</option>
                                    <option value="on_date"           {{ old('recurrence_ends') === 'on_date'           ? 'selected' : '' }}>On Specific Date</option>
                                </select>
                                @error('recurrence_ends')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>

                        {{-- After X Occurrences --}}
                        <div id="section_occurrences" class="mb-3" style="display:none">
                            <label for="occurrences" class="form-label fw-semibold">Number of Occurrences</label>
                            <input type="number" id="occurrences" name="occurrences"
                                   value="{{ old('occurrences', 1) }}" min="1"
                                   class="form-control @error('occurrences') is-invalid @enderror"
                                   style="max-width:200px">
                            @error('occurrences')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- On Specific Date --}}
                        <div id="section_end_date" class="mb-3" style="display:none">
                            <label for="end_date" class="form-label fw-semibold">End Date</label>
                            <input type="date" id="end_date" name="end_date"
                                   value="{{ old('end_date') }}"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   style="max-width:260px">
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Weekly: day checkboxes --}}
                        <div id="section_week_days" class="mb-3" style="display:none">
                            <label class="form-label fw-semibold">
                                Repeat on these days <span class="text-danger">*</span>
                            </label>
                            @error('week_days')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            <div class="row g-2">
                                @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $num => $day)
                                <div class="col-sm-3 col-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                               id="wd_{{ $num }}" name="week_days[]" value="{{ $num }}"
                                               {{ in_array($num, old('week_days', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="wd_{{ $num }}">{{ $day }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Monthly: day of month + optional months --}}
                        <div id="section_monthly" class="mb-3" style="display:none">
                            <div class="mb-3">
                                <label for="month_day" class="form-label fw-semibold">
                                    Repeat on date of month <span class="text-danger">*</span>
                                </label>
                                <select id="month_day" name="month_day"
                                        class="form-select @error('month_day') is-invalid @enderror"
                                        style="max-width:200px">
                                    @for($d = 1; $d <= 31; $d++)
                                        <option value="{{ $d }}" {{ old('month_day', 1) == $d ? 'selected' : '' }}>
                                            {{ $d }}{{ in_array($d,[1,21,31])?'st':(in_array($d,[2,22])?'nd':(in_array($d,[3,23])?'rd':'th')) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('month_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Select months <span class="text-muted fw-normal">(leave blank for all months)</span></label>
                                <div class="row g-2">
                                    @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $month)
                                    <div class="col-sm-3 col-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                   id="mo_{{ $num }}" name="months[]" value="{{ $num }}"
                                                   {{ in_array($num, old('months', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mo_{{ $num }}">{{ $month }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>{{-- /section_repetitive --}}

                    <div class="mt-3" style="max-width:260px">
                        <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                        <input type="time" id="scheduled_time" name="scheduled_time"
                               value="{{ old('scheduled_time') }}"
                               class="form-control @error('scheduled_time') is-invalid @enderror">
                        @error('scheduled_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-check-lg me-1"></i>Add Task
                </button>
                <a href="{{ route('task.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleRecurrence(checked) {
    document.getElementById('section_deadline').style.display   = checked ? 'none' : '';
    document.getElementById('section_repetitive').style.display = checked ? '' : 'none';
    if (checked) {
        syncType();
        syncEnds();
    } else {
        document.getElementById('section_occurrences').style.display = 'none';
        document.getElementById('section_end_date').style.display    = 'none';
        document.getElementById('section_week_days').style.display   = 'none';
        document.getElementById('section_monthly').style.display     = 'none';
    }
}

function syncType() {
    const type = document.getElementById('recurrence_type').value;
    document.getElementById('section_week_days').style.display = type === 'weekly'  ? '' : 'none';
    document.getElementById('section_monthly').style.display   = type === 'monthly' ? '' : 'none';
}

function syncEnds() {
    const ends = document.getElementById('recurrence_ends').value;
    document.getElementById('section_occurrences').style.display = ends === 'after_occurrences' ? '' : 'none';
    document.getElementById('section_end_date').style.display    = ends === 'on_date'           ? '' : 'none';
}

// Restore state after validation failure
document.addEventListener('DOMContentLoaded', function () {
    const isRep = document.getElementById('is_repetitive').checked;
    toggleRecurrence(isRep);
    if (isRep) { syncType(); syncEnds(); }
});
</script>
@endpush
