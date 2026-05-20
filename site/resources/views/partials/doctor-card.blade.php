@php
    $specName = $doctor->specialization->name ?? '';
    $ageAttr = match ($doctor->patient_age) {
        'adults' => 'adults',
        'children' => 'children',
        default => 'all',
    };
    $bookingBtnHref = $doctorBookingUrl ?? route('booking.start', ['from' => 'doctor:'.$doctor->slug]);
@endphp
<article class="doctor-card animate-on-scroll"
         data-specialization="{{ $specName }}"
         data-category="{{ $doctor->category_label }}"
         data-patient-age="{{ $ageAttr }}"
         data-full-name="{{ mb_strtolower($doctor->full_name) }}"
         data-url="{{ route('doctors.show', $doctor->slug) }}">
    <div class="doctor-photo-wrap">
        <div class="doctor-photo">
            <a href="{{ route('doctors.show', $doctor->slug) }}">
                <img src="{{ asset($doctor->photo ?: 'images/doctors/doctor-placeholder.jpg') }}" alt="{{ $doctor->full_name }}">
            </a>
        </div>
    </div>
    <div class="doctor-content">
        <span class="doctor-badge">{{ $specName }}</span>
        <h3 class="doctor-name">
            <a href="{{ route('doctors.show', $doctor->slug) }}">
                {{ $doctor->first_name }} {{ $doctor->middle_name }}<br>{{ $doctor->last_name }}
            </a>
        </h3>
        <p class="doctor-row"><span class="doctor-label">Стаж работы:</span> <span class="doctor-experience-value">{{ $doctor->experience_years }} лет</span></p>
        <p class="doctor-row"><span class="doctor-label">Категория:</span> <span class="doctor-category-value">{{ $doctor->category_label }}</span></p>
        <a href="{{ $bookingBtnHref }}" class="doctor-btn">Записаться</a>
    </div>
</article>
