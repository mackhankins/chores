# Chores

A family chore management app built with Laravel, Filament, and Livewire. Parents configure chores by room and set up fixed or rotating assignment schedules through a Filament admin panel. Kids check in with a 4-digit PIN to see their daily tasks and mark them complete on a mobile-friendly dashboard.

## Features

- **Room-based chore organization** — group chores by room (Kitchen, Bathroom, Yard, etc.) with emoji icons and custom sort order
- **Flexible assignment** — assign chores to a specific child or to a rotation group that automatically cycles through kids on daily, weekly, biweekly, or monthly schedules
- **Bulk assignment** — select multiple chores at once when creating assignments
- **Kid dashboard** — mobile-friendly check-in with a simple 4-digit PIN, progress bar, and tap-to-complete interface
- **SMS notifications** — morning chore lists and evening reminders via mail-to-SMS carrier gateways (Verizon, AT&T, T-Mobile, Sprint) through Gmail SMTP
- **Vacation tracking** — pause assignments when kids are away
- **Missed chore carryover** — opt-in per chore; missed tasks reappear on the dashboard for up to 7 days (configurable) until completed
- **Completion reporting** — dashboard stats widget with per-child monthly completion rates and sparkline charts, plus a detailed report page with period/child filters, per-chore breakdown, and a missed-only view that lets parents retroactively credit forgotten chores
- **Chore earnings** — assign dollar values to chores; kids see a progress bar on their dashboard tracking what they've earned for the month
- **Expense deduction mode** — kids with monthly expenses (rent, cellphone, gym, etc.) have earnings automatically deducted from that total, with a progress bar showing how much they've paid down; supports logging cash payments (e.g., bi-weekly)
- **Self-hosted** — runs on Docker with a multi-stage build, deployed via GitHub Actions to GHCR

## Tech Stack

- **PHP 8.4** / **Laravel 12**
- **Filament 5** — admin panel
- **Livewire 4** — kid-facing UI
- **Tailwind CSS 4**
- **SQLite** — database
- **Docker** — multi-stage build (Composer, Node, PHP/Apache)

## Local Development

```bash
# Clone and install
git clone https://github.com/mackhankins/chores.git
cd chores
composer install
npm install
cp .env.example .env
php artisan key:generate

# Set up database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Run dev server
composer run dev
```

The admin panel is at `/admin` (seeded credentials: `admin@admin.com` / `password`).
The kid dashboard is at `/`.

## Docker Deployment

The app is designed to self-host on a NAS or any Docker host.

### Build and run

```bash
docker compose up -d
```

### Services

| Service | Purpose |
|---------|---------|
| `app` | Laravel + Apache on port 8088 |
| `scheduler` | Runs `schedule:work` for notifications and nightly reconciliation |
| `queue` | Processes queued jobs |

### Data persistence

All persistent data lives on bind-mounted volumes:

- `/share/chores/data` — SQLite database
- `/share/chores/storage/logs` — application logs
- `/share/chores/storage/framework/sessions` — sessions

### First-time setup

```bash
# Create admin user
docker exec -it chores-app php artisan make:filament-user

# Test SMS notifications
docker exec -it chores-app php artisan chores:notify --test-child=ChildName
```

### Updating

```bash
docker compose pull && docker compose up -d
```

Migrations run automatically on container start.

## Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `CHORES_CARRYOVER_DAYS` | `7` | Days a missed chore carries over before expiring |
| `MAIL_MAILER` | `smtp` | Set to `smtp` for Gmail |
| `MAIL_HOST` | — | `smtp.gmail.com` for Gmail |
| `MAIL_USERNAME` | — | Gmail address |
| `MAIL_PASSWORD` | — | Gmail app password |

### SMS Notifications

SMS is sent via mail-to-SMS carrier gateways (no Twilio or third-party SMS API required). Configure each child's phone number and carrier in the admin panel. Supported carriers:

- Verizon (`vtext.com`)
- AT&T (`txt.att.net`)
- T-Mobile (`tmomail.net`)
- Sprint (`messaging.sprintpcs.com`)

Notifications are configured per-child with separate morning and evening times.

## Artisan Commands

| Command | Schedule | Description |
|---------|----------|-------------|
| `chores:notify` | Every minute | Sends morning chore lists and evening reminders based on per-child notification times |
| `chores:reconcile` | Daily at midnight | Records missed chores for carryover-eligible tasks |
| `chores:notify --test-child=Name` | Manual | Sends both notifications immediately to a specific child for testing |
| `chores:reconcile --date=YYYY-MM-DD` | Manual | Reconcile a specific date instead of yesterday |

## License

MIT
