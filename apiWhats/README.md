<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## WhatsApp Cloud API webhook (test setup)

The public callback endpoint is `GET` and `POST /webhooks/whatsapp`.

1. Install the backend dependencies and prepare the local environment:

   ```sh
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

   Set `WHATSAPP_VERIFY_TOKEN` to a private verification string you choose, and
   `META_APP_SECRET` to the App Secret shown in your Meta app settings. Do not
   commit `.env` or share either value. If Laravel configuration is cached,
   refresh it after changing these settings with `php artisan config:clear`.

2. Start Laravel so a tunnel can reach it:

   ```sh
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. Expose the local server through an HTTPS tunnel, for example with
   [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/do-more-with-tunnels/trycloudflare/):

   ```sh
   cloudflared tunnel --url http://localhost:8000
   ```

   Use the HTTPS hostname printed by the tunnel. Keep the tunnel and Laravel
   server running while configuring and testing the webhook.

4. In the Meta app dashboard, open **WhatsApp > Configuration > Webhooks**
   (or the webhook configuration for the WhatsApp product), then enter:

   - **Callback URL:** `https://<HTTPS-hostname-from-your-tunnel>/webhooks/whatsapp`
   - **Verify token:** the exact value configured in `WHATSAPP_VERIFY_TOKEN`

   Save/verify the callback. Subscribe to the `messages` webhook field, then
   use Meta's **Test** action for that field to send a test event. A successful
   delivery receives HTTP 200. The endpoint logs only event type and safe
   identifiers; it does not log message contents or phone numbers and does not
   send automatic replies.

   The POST handler validates `X-Hub-Signature-256` using the raw request body
   and `META_APP_SECRET`; unsigned or incorrectly signed requests are rejected.
