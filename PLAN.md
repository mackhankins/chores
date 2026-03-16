# Chore Tracker - Project Plan

## Overview
A family chore management app built with Laravel & Filament. Parents assign chores (fixed or rotating) grouped by room. Kids check in with a PIN to see and complete their chores. Future: daily SMS notifications.

---

## Data Model

### Children
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| name | string | |
| phone | string (nullable) | For future SMS |
| pin | string (4 digits) | Simple auth for kid check-in |
| avatar_color | string | Visual identifier |

### Rooms
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| name | string | Kitchen, Bathroom, Yard, etc. |
| icon | string (nullable) | Emoji or icon name |
| sort_order | integer | Display ordering |

### Chores
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| name | string | "Take out trash", "Wipe counters" |
| description | text (nullable) | Optional detailed instructions |
| room_id | foreign key | Belongs to a room |
| is_active | boolean | Soft enable/disable |

### Rotation Groups
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| name | string | "Kitchen Rotation", "Bathroom Rotation" |
| period | enum | daily, weekly, biweekly, monthly |
| start_date | date | Reference point for calculating whose turn it is |

### Rotation Group Members (pivot)
| Field | Type | Notes |
|-------|------|-------|
| rotation_group_id | foreign key | |
| child_id | foreign key | |
| position | integer | Order in rotation (0, 1, 2...) |

### Chore Assignments
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| chore_id | foreign key (nullable) | Set for single-chore assignments |
| room_id | foreign key (nullable) | Set for room-level assignments (all active chores) |
| child_id | foreign key (nullable) | Set for fixed assignments |
| rotation_group_id | foreign key (nullable) | Set for rotating assignments |
| | | **One of chore_id or room_id must be set** |
| | | **One of child_id or rotation_group_id must be set** |

### Chore Completions
| Field | Type | Notes |
|-------|------|-------|
| id | ulid | |
| chore_id | foreign key | |
| child_id | foreign key | Who completed it |
| completed_date | date | The date it was completed for |
| created_at | timestamp | When they marked it done |

---

## Rotation Logic

Given a rotation group with `period`, `start_date`, and N ordered members:

```
periods_elapsed = floor((current_date - start_date) / period_length)
current_position = periods_elapsed % number_of_members
assigned_child = members[current_position]
```

- **daily** = advances every day
- **weekly** = advances every Monday (or start_date weekday)
- **biweekly** = advances every 2 weeks
- **monthly** = advances on the same day each month

---

## Interfaces

### 1. Admin Panel (Filament) — Parent-facing
- **Dashboard**: Today's chores per child, completion status
- **Children Resource**: CRUD children, set PINs
- **Rooms Resource**: CRUD rooms
- **Chores Resource**: CRUD chores, assign to room
- **Rotation Groups Resource**: Create groups, set period, order children
- **Assignments Resource**: Assign chores (fixed to child OR to rotation group)
- **Completion Log**: View history of completions

### 2. Kid Check-in (Blade/Livewire) — Kid-facing
- **PIN Entry**: Simple numeric keypad, enter 4-digit PIN
- **My Chores Today**: List of assigned chores for the day with room grouping
- **Mark Complete**: Tap/click to mark done, with confirmation
- **Simple, mobile-friendly UI** — big buttons, clear text, fun colors

---

## Build Phases

### Phase 1 — Foundation (Current)
- [x] Laravel project scaffolded
- [x] Install Filament
- [x] Create migrations for all tables
- [x] Create Eloquent models with relationships
- [x] Implement rotation calculation service

### Phase 2 — Admin Panel
- [x] Filament resources: Children, Rooms, Chores
- [x] Filament resources: Rotation Groups with member ordering
- [x] Filament resources: Chore Assignments (fixed vs rotating)
- [x] Dashboard widget: Today's chores overview

### Phase 3 — Kid Check-in
- [x] PIN entry page (route: `/`)
- [x] "My Chores Today" view with room grouping
- [x] Mark complete functionality
- [x] Simple session-based auth (PIN → child, expires same day)

### Phase 4 — SMS Notifications
- [x] Integrate Twilio
- [x] Scheduled command: `chores:notify` (runs every minute)
- [x] Morning notification: chore list + dashboard link, per-kid time
- [x] Evening reminder: remaining chores + dashboard link, per-kid time

### Phase 5 — Deployment (QNAP Docker)
- [x] Dockerfile: multi-stage build (Node for Vite, PHP 8.3 Apache)
- [x] GitHub Actions: build & push to GHCR on push to main
- [x] docker-compose.yml with app, scheduler, and queue services
- [ ] Switch to bind mount volumes so database survives container recreation
  - Change `sqlite-data:/var/www/html/database` → `/share/Container/chores/database:/var/www/html/database`
  - Same for `app-storage` → `/share/Container/chores/storage:/var/www/html/storage`
  - Remove named `volumes:` section at bottom
  - Create dirs on QNAP first: `mkdir -p /share/Container/chores/{database,storage}`
- [ ] Twilio toll-free verification pending (required to send SMS in US)

#### Deployment Notes
- **Container Station image cache**: After GitHub Actions builds a new image, you must pull the fresh image from Container Station's **Images** menu before recreating the app. It caches aggressively.
- **Seeding in production**: `docker exec -it chores-app php /var/www/html/artisan db:seed --force`
- **Create admin user**: `docker exec -it chores-app php /var/www/html/artisan make:filament-user`
- **Test SMS**: `docker exec -it chores-app php /var/www/html/artisan chores:notify --test-child=ChildName`

---

## Tech Stack
- **Laravel 12** — backend framework
- **Filament 3** — admin panel
- **SQLite** — database (easy local dev, upgrade to MySQL/Postgres later if needed)
- **Livewire** — kid-facing check-in UI
- **Tailwind CSS** — styling
