<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Онлайн-запись | {{ config('app.name') }}</title>
<meta name="description" content="Запись к мастеру онлайн: выберите услугу, мастера и свободное время.">
<style>
  :root { --bg:#f7f4fb; --card:#fff; --text:#24202b; --muted:#6f6879; --accent:#7b4fb8; --accent-2:#efe7fa; --line:#e6e0ee; --ok:#2e7d5b; --err:#b3261e; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); line-height:1.5; }
  .wrap { max-width:1040px; margin:0 auto; padding:0 16px; }
  header { padding:40px 0 24px; }
  .brand { font-weight:700; color:var(--accent); letter-spacing:.02em; }
  h1 { font-size:clamp(28px,4.5vw,42px); margin:6px 0 8px; line-height:1.15; }
  .lead { color:var(--muted); max-width:620px; margin:0; }
  .grid { display:grid; gap:24px; grid-template-columns:1fr; padding-bottom:56px; }
  @media (min-width:880px) { .grid { grid-template-columns:1.2fr 1fr; } }
  .card { background:var(--card); border-radius:16px; padding:22px; box-shadow:0 1px 3px rgba(40,20,70,.06),0 10px 30px rgba(40,20,70,.06); }
  h2 { font-size:19px; margin:0 0 14px; }
  .step { font-size:13px; color:var(--accent); font-weight:600; margin:18px 0 8px; text-transform:uppercase; letter-spacing:.05em; }
  .step:first-of-type { margin-top:0; }
  .services { display:grid; gap:10px; }
  .opt { display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px 14px; border:1.5px solid var(--line); border-radius:12px; cursor:pointer; background:#fff; text-align:left; font:inherit; color:inherit; width:100%; }
  .opt:hover { border-color:#cbb8e6; }
  .opt[aria-pressed="true"] { border-color:var(--accent); background:var(--accent-2); }
  .opt small { color:var(--muted); display:block; }
  .price { font-weight:700; white-space:nowrap; }
  .chips { display:flex; flex-wrap:wrap; gap:8px; }
  .chip { padding:8px 14px; border:1.5px solid var(--line); border-radius:999px; background:#fff; cursor:pointer; font:inherit; color:inherit; min-height:40px; }
  .chip[aria-pressed="true"] { border-color:var(--accent); background:var(--accent); color:#fff; }
  .muted { color:var(--muted); font-size:14px; }
  label { display:block; font-size:14px; margin:12px 0 4px; }
  input[type=text], input[type=tel], input[type=date], textarea { width:100%; padding:11px 12px; border:1.5px solid var(--line); border-radius:10px; font:inherit; background:#fff; }
  input:focus, textarea:focus, .opt:focus-visible, .chip:focus-visible, button:focus-visible { outline:3px solid #c9b2ea; outline-offset:1px; }
  .consent { display:flex; gap:8px; align-items:flex-start; font-size:13px; color:var(--muted); margin-top:12px; }
  .consent input { margin-top:3px; }
  .submit { margin-top:16px; width:100%; padding:14px; border:0; border-radius:12px; background:var(--accent); color:#fff; font:inherit; font-weight:600; cursor:pointer; min-height:48px; }
  .submit:disabled { opacity:.5; cursor:not-allowed; }
  .summary { background:var(--accent-2); border-radius:12px; padding:12px 14px; font-size:14px; margin-top:14px; }
  .alert { border-radius:12px; padding:14px 16px; margin-bottom:18px; }
  .alert.ok { background:#e5f4ec; color:var(--ok); }
  .alert.err { background:#fbe9e7; color:var(--err); }
  .hp { position:absolute; left:-9999px; }
  footer { color:var(--muted); font-size:13px; padding:0 0 32px; }
</style>
</head>
<body>
<div class="wrap">
  <header>
    <div class="brand">{{ config('app.name') }}</div>
    <h1>Запись к мастеру онлайн</h1>
    <p class="lead">Выберите услугу, мастера и удобное время. Показываем только свободные окна, подтверждение придёт по телефону.</p>
  </header>

  @if (session('booked'))
    <div class="alert ok" role="status">
      Вы записаны: {{ session('booked.service') }}, мастер {{ session('booked.master') }}, {{ session('booked.when') }}. Ждём вас!
    </div>
  @endif
  @if ($errors->any())
    <div class="alert err" role="alert">{{ $errors->first() }}</div>
  @endif

  <div class="grid">
    <section class="card" aria-labelledby="pick">
      <h2 id="pick">Выбор услуги и времени</h2>

      <div class="step">1. Услуга</div>
      <div class="services">
        @foreach ($services as $service)
          <button type="button" class="opt" data-service="{{ $service->id }}" data-duration="{{ $service->duration_minutes }}" data-name="{{ $service->name }}" aria-pressed="false">
            <span>{{ $service->name }}<small>{{ $service->duration_minutes }} мин</small></span>
            <span class="price">{{ number_format($service->price, 0, ',', ' ') }} руб.</span>
          </button>
        @endforeach
      </div>

      <div class="step">2. Мастер</div>
      <div class="chips" id="masters"><span class="muted">Сначала выберите услугу</span></div>

      <div class="step">3. Дата и время</div>
      <input type="date" id="date" value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}" max="{{ now()->addDays(30)->format('Y-m-d') }}" aria-label="Дата">
      <div class="chips" id="slots" style="margin-top:10px"><span class="muted">Выберите мастера, чтобы увидеть свободное время</span></div>
    </section>

    <section class="card" aria-labelledby="contacts">
      <h2 id="contacts">Ваши контакты</h2>
      <form method="post" action="{{ route('booking.store') }}" id="form">
        @csrf
        <input type="hidden" name="service_id" id="service_id">
        <input type="hidden" name="master_id" id="master_id">
        <input type="hidden" name="starts_at" id="starts_at">
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

        <label for="client_name">Имя</label>
        <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}" required minlength="2" maxlength="80" autocomplete="name">

        <label for="client_phone">Телефон</label>
        <input type="tel" id="client_phone" name="client_phone" value="{{ old('client_phone') }}" required placeholder="+7 900 000-00-00" autocomplete="tel" inputmode="tel">

        <label for="comment">Комментарий (необязательно)</label>
        <textarea id="comment" name="comment" rows="3" maxlength="500">{{ old('comment') }}</textarea>

        <label class="consent"><input type="checkbox" name="consent" value="1" required> Согласна(сен) на обработку персональных данных для записи</label>

        <div class="summary" id="summary">Выберите услугу, мастера и время слева.</div>
        <button class="submit" id="submit" type="submit" disabled>Записаться</button>
      </form>
    </section>
  </div>
  <footer>Демо-проект. Данные студии и мастеров вымышленные.</footer>
</div>

<script>
(() => {
  const state = { service: null, master: null, slot: null };
  const $ = (id) => document.getElementById(id);
  const mastersBox = $('masters'), slotsBox = $('slots'), dateInput = $('date');

  const press = (list, el) => list.forEach(b => b.setAttribute('aria-pressed', b === el ? 'true' : 'false'));

  function render() {
    $('service_id').value = state.service?.id ?? '';
    $('master_id').value = state.master?.id ?? '';
    $('starts_at').value = state.slot ? `${dateInput.value} ${state.slot}` : '';
    const ready = state.service && state.master && state.slot;
    $('submit').disabled = !ready;
    $('summary').textContent = ready
      ? `${state.service.name}, мастер ${state.master.name}, ${new Date(dateInput.value).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' })} в ${state.slot}`
      : 'Выберите услугу, мастера и время слева.';
  }

  async function loadMasters() {
    mastersBox.innerHTML = '<span class="muted">Загружаем мастеров…</span>';
    const list = await (await fetch(`/api/masters?service_id=${state.service.id}`)).json();
    mastersBox.innerHTML = '';
    if (!list.length) { mastersBox.innerHTML = '<span class="muted">Нет мастеров на эту услугу</span>'; return; }
    list.forEach(m => {
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'chip'; b.setAttribute('aria-pressed', 'false');
      b.textContent = `${m.name} · ${m.specialty}`;
      b.onclick = () => { press([...mastersBox.children], b); state.master = m; state.slot = null; loadSlots(); render(); };
      mastersBox.appendChild(b);
    });
  }

  async function loadSlots() {
    if (!state.service || !state.master) return;
    slotsBox.innerHTML = '<span class="muted">Ищем свободное время…</span>';
    const q = new URLSearchParams({ master_id: state.master.id, service_id: state.service.id, date: dateInput.value });
    const list = await (await fetch(`/api/slots?${q}`)).json();
    slotsBox.innerHTML = '';
    if (!Array.isArray(list) || !list.length) { slotsBox.innerHTML = '<span class="muted">На эту дату свободных окон нет, выберите другой день</span>'; return; }
    list.forEach(t => {
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'chip'; b.textContent = t; b.setAttribute('aria-pressed', 'false');
      b.onclick = () => { press([...slotsBox.children], b); state.slot = t; render(); };
      slotsBox.appendChild(b);
    });
  }

  document.querySelectorAll('[data-service]').forEach(b => b.onclick = () => {
    press([...document.querySelectorAll('[data-service]')], b);
    state.service = { id: b.dataset.service, name: b.dataset.name };
    state.master = null; state.slot = null;
    slotsBox.innerHTML = '<span class="muted">Выберите мастера, чтобы увидеть свободное время</span>';
    loadMasters(); render();
  });
  dateInput.onchange = () => { state.slot = null; loadSlots(); render(); };

  // Маска телефона: +7 XXX XXX-XX-XX
  $('client_phone').addEventListener('input', (e) => {
    let d = e.target.value.replace(/\D/g, '');
    if (d.startsWith('8')) d = '7' + d.slice(1);
    if (!d.startsWith('7')) d = '7' + d;
    d = d.slice(0, 11);
    const p = [d.slice(1, 4), d.slice(4, 7), d.slice(7, 9), d.slice(9, 11)];
    e.target.value = '+7' + (p[0] ? ' ' + p[0] : '') + (p[1] ? ' ' + p[1] : '') + (p[2] ? '-' + p[2] : '') + (p[3] ? '-' + p[3] : '');
  });
})();
</script>
</body>
</html>
