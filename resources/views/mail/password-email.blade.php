<x-mail::message>
# Задание пароля

Для того чтобы задать пароль для вашей учетной записи, перейдите по следующей ссылке:

<x-mail::button :url="$url">
Задать пароль
</x-mail::button>

Спасибо,<br>
{{ config('app.name') }}
</x-mail::message>
