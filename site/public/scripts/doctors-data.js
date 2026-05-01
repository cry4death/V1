(function () {
  'use strict';

  function formatExperienceYears(years) {
    var n = years % 100;
    if (n >= 11 && n <= 14) return years + ' лет';
    var d = years % 10;
    if (d === 1) return years + ' год';
    if (d >= 2 && d <= 4) return years + ' года';
    return years + ' лет';
  }

  window.formatExperienceYears = formatExperienceYears;

  window.DOCTORS_DATA = [
    { fullName: 'Денисевич Юлия Александровна', specialization: 'Акушер-гинеколог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 8, photo: 'images/doctors/doctor-f1.jpg', alt: 'Денисевич Юлия Александровна' },
    { fullName: 'Иванов Пётр Сергеевич', specialization: 'Терапевт', category: 'Первая категория', patientAge: 'all', experienceYears: 11, photo: 'images/doctors/doctor-m1.jpg', alt: 'Иванов Пётр Сергеевич' },
    { fullName: 'Козлова Анна Викторовна', specialization: 'Педиатр', category: 'Вторая категория', patientAge: 'children', experienceYears: 7, photo: 'images/doctors/doctor-f2.jpg', alt: 'Козлова Анна Викторовна' },
    { fullName: 'Сидоров Михаил Андреевич', specialization: 'Кардиолог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 14, photo: 'images/doctors/doctor-m2.jpg', alt: 'Сидоров Михаил Андреевич' },
    { fullName: 'Новикова Елена Дмитриевна', specialization: 'Невролог', category: 'Первая категория', patientAge: 'all', experienceYears: 10, photo: 'images/doctors/doctor-f3.jpg', alt: 'Новикова Елена Дмитриевна' },
    { fullName: 'Морозов Дмитрий Игоревич', specialization: 'Офтальмолог', category: 'Вторая категория', patientAge: 'all', experienceYears: 6, photo: 'images/doctors/doctor-m3.jpg', alt: 'Морозов Дмитрий Игоревич' },
    { fullName: 'Волкова Ольга Николаевна', specialization: 'Дерматолог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 12, photo: 'images/doctors/doctor-m1.jpg', alt: 'Волкова Ольга Николаевна' },
    { fullName: 'Федоров Андрей Владимирович', specialization: 'Стоматолог', category: 'Первая категория', patientAge: 'all', experienceYears: 9, photo: 'images/doctors/doctor-m4.jpg', alt: 'Федоров Андрей Владимирович' },
    { fullName: 'Соколова Татьяна Павловна', specialization: 'Акушер-гинеколог', category: 'Вторая категория', patientAge: 'adults', experienceYears: 5, photo: 'images/doctors/doctor-m1.jpg', alt: 'Соколова Татьяна Павловна' },
    { fullName: 'Лебедев Игорь Александрович', specialization: 'Терапевт', category: 'Высшая категория', patientAge: 'adults', experienceYears: 16, photo: 'images/doctors/doctor-m5.jpg', alt: 'Лебедев Игорь Александрович' },
    { fullName: 'Егорова Мария Сергеевна', specialization: 'Педиатр', category: 'Первая категория', patientAge: 'children', experienceYears: 8, photo: 'images/doctors/doctor-m1.jpg', alt: 'Егорова Мария Сергеевна' },
    { fullName: 'Павлов Николай Олегович', specialization: 'Кардиолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 7, photo: 'images/doctors/doctor-m1.jpg', alt: 'Павлов Николай Олегович' },
    { fullName: 'Кузнецова Светлана Игоревна', specialization: 'Терапевт', category: 'Вторая категория', patientAge: 'all', experienceYears: 6, photo: 'images/doctors/doctor-f1.jpg', alt: 'Кузнецова Светлана Игоревна' },
    { fullName: 'Орлова Наталья Владимировна', specialization: 'Невролог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 13, photo: 'images/doctors/doctor-f2.jpg', alt: 'Орлова Наталья Владимировна' },
    { fullName: 'Семёнов Виктор Петрович', specialization: 'Кардиолог', category: 'Вторая категория', patientAge: 'adults', experienceYears: 8, photo: 'images/doctors/doctor-m2.jpg', alt: 'Семёнов Виктор Петрович' },
    { fullName: 'Васильева Ирина Андреевна', specialization: 'Педиатр', category: 'Высшая категория', patientAge: 'children', experienceYears: 11, photo: 'images/doctors/doctor-m1.jpg', alt: 'Васильева Ирина Андреевна' },
    { fullName: 'Михайлов Сергей Дмитриевич', specialization: 'Офтальмолог', category: 'Первая категория', patientAge: 'all', experienceYears: 9, photo: 'images/doctors/doctor-m3.jpg', alt: 'Михайлов Сергей Дмитриевич' },
    { fullName: 'Фролова Екатерина Олеговна', specialization: 'Дерматолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 7, photo: 'images/doctors/doctor-m1.jpg', alt: 'Фролова Екатерина Олеговна' },
    { fullName: 'Громов Александр Николаевич', specialization: 'Стоматолог', category: 'Высшая категория', patientAge: 'all', experienceYears: 15, photo: 'images/doctors/doctor-m4.jpg', alt: 'Громов Александр Николаевич' },
    { fullName: 'Белова Ольга Сергеевна', specialization: 'Акушер-гинеколог', category: 'Первая категория', patientAge: 'adults', experienceYears: 10, photo: 'images/doctors/doctor-m1.jpg', alt: 'Белова Ольга Сергеевна' },
    { fullName: 'Киселёв Денис Игоревич', specialization: 'Терапевт', category: 'Вторая категория', patientAge: 'adults', experienceYears: 5, photo: 'images/doctors/doctor-m1.jpg', alt: 'Киселёв Денис Игоревич' },
    { fullName: 'Макарова Анна Викторовна', specialization: 'Педиатр', category: 'Вторая категория', patientAge: 'children', experienceYears: 6, photo: 'images/doctors/doctor-f2.jpg', alt: 'Макарова Анна Викторовна' },
    { fullName: 'Зайцев Роман Александрович', specialization: 'Кардиолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 12, photo: 'images/doctors/doctor-m2.jpg', alt: 'Зайцев Роман Александрович' },
    { fullName: 'Степанова Марина Павловна', specialization: 'Невролог', category: 'Вторая категория', patientAge: 'all', experienceYears: 7, photo: 'images/doctors/doctor-f3.jpg', alt: 'Степанова Марина Павловна' },
    { fullName: 'Виноградов Андрей Владимирович', specialization: 'Офтальмолог', category: 'Высшая категория', patientAge: 'all', experienceYears: 14, photo: 'images/doctors/doctor-m3.jpg', alt: 'Виноградов Андрей Владимирович' },
    { fullName: 'Ковалёва Татьяна Николаевна', specialization: 'Дерматолог', category: 'Вторая категория', patientAge: 'adults', experienceYears: 8, photo: 'images/doctors/doctor-f1.jpg', alt: 'Ковалёва Татьяна Николаевна' },
    { fullName: 'Борисов Илья Сергеевич', specialization: 'Стоматолог', category: 'Первая категория', patientAge: 'all', experienceYears: 10, photo: 'images/doctors/doctor-m4.jpg', alt: 'Борисов Илья Сергеевич' },
    { fullName: 'Герасимова Юлия Дмитриевна', specialization: 'Акушер-гинеколог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 11, photo: 'images/doctors/doctor-f1.jpg', alt: 'Герасимова Юлия Дмитриевна' },
    { fullName: 'Тарасов Максим Олегович', specialization: 'Терапевт', category: 'Первая категория', patientAge: 'all', experienceYears: 9, photo: 'images/doctors/doctor-m5.jpg', alt: 'Тарасов Максим Олегович' },
    { fullName: 'Романова Елена Игоревна', specialization: 'Педиатр', category: 'Первая категория', patientAge: 'children', experienceYears: 7, photo: 'images/doctors/doctor-m1.jpg', alt: 'Романова Елена Игоревна' },
    { fullName: 'Данилов Павел Александрович', specialization: 'Кардиолог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 16, photo: 'images/doctors/doctor-m1.jpg', alt: 'Данилов Павел Александрович' },
    { fullName: 'Жукова Виктория Сергеевна', specialization: 'Невролог', category: 'Первая категория', patientAge: 'all', experienceYears: 6, photo: 'images/doctors/doctor-f3.jpg', alt: 'Жукова Виктория Сергеевна' },
    { fullName: 'Никитин Артём Владимирович', specialization: 'Офтальмолог', category: 'Первая категория', patientAge: 'all', experienceYears: 8, photo: 'images/doctors/doctor-m3.jpg', alt: 'Никитин Артём Владимирович' },
    { fullName: 'Лазарева Дарья Петровна', specialization: 'Дерматолог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 12, photo: 'images/doctors/doctor-f1.jpg', alt: 'Лазарева Дарья Петровна' },
    { fullName: 'Сорокин Глеб Николаевич', specialization: 'Стоматолог', category: 'Вторая категория', patientAge: 'all', experienceYears: 5, photo: 'images/doctors/doctor-m4.jpg', alt: 'Сорокин Глеб Николаевич' },
    { fullName: 'Воронова Анастасия Андреевна', specialization: 'Акушер-гинеколог', category: 'Первая категория', patientAge: 'adults', experienceYears: 9, photo: 'images/doctors/doctor-m1.jpg', alt: 'Воронова Анастасия Андреевна' },
    { fullName: 'Медведев Кирилл Олегович', specialization: 'Терапевт', category: 'Высшая категория', patientAge: 'adults', experienceYears: 13, photo: 'images/doctors/doctor-m1.jpg', alt: 'Медведев Кирилл Олегович' },
    { fullName: 'Ефимова Надежда Викторовна', specialization: 'Педиатр', category: 'Высшая категория', patientAge: 'children', experienceYears: 10, photo: 'images/doctors/doctor-f2.jpg', alt: 'Ефимова Надежда Викторовна' },
    { fullName: 'Крылов Станислав Дмитриевич', specialization: 'Кардиолог', category: 'Вторая категория', patientAge: 'adults', experienceYears: 7, photo: 'images/doctors/doctor-m2.jpg', alt: 'Крылов Станислав Дмитриевич' },
    { fullName: 'Тихонова Валерия Сергеевна', specialization: 'Невролог', category: 'Высшая категория', patientAge: 'all', experienceYears: 11, photo: 'images/doctors/doctor-f3.jpg', alt: 'Тихонова Валерия Сергеевна' },
    { fullName: 'Куликов Евгений Игоревич', specialization: 'Офтальмолог', category: 'Вторая категория', patientAge: 'all', experienceYears: 6, photo: 'images/doctors/doctor-m3.jpg', alt: 'Куликов Евгений Игоревич' },
    { fullName: 'Савельева Ирина Павловна', specialization: 'Дерматолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 8, photo: 'images/doctors/doctor-f1.jpg', alt: 'Савельева Ирина Павловна' },
    { fullName: 'Филиппов Олег Александрович', specialization: 'Стоматолог', category: 'Первая категория', patientAge: 'all', experienceYears: 14, photo: 'images/doctors/doctor-m4.jpg', alt: 'Филиппов Олег Александрович' },
    { fullName: 'Кузнецов Владимир Сергеевич', specialization: 'Проктолог', category: 'Высшая категория', patientAge: 'adults', experienceYears: 12, photo: 'images/doctors/doctor-m1.jpg', alt: 'Кузнецов Владимир Сергеевич' },
    { fullName: 'Смирнова Ольга Игоревна', specialization: 'Проктолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 8, photo: 'images/doctors/doctor-f1.jpg', alt: 'Смирнова Ольга Игоревна' },
    { fullName: 'Петров Андрей Викторович', specialization: 'Проктолог', category: 'Вторая категория', patientAge: 'adults', experienceYears: 6, photo: 'images/doctors/doctor-m2.jpg', alt: 'Петров Андрей Викторович' },
    { fullName: 'Новикова Татьяна Александровна', specialization: 'Проктолог', category: 'Первая категория', patientAge: 'adults', experienceYears: 10, photo: 'images/doctors/doctor-f2.jpg', alt: 'Новикова Татьяна Александровна' }
  ];
})();
