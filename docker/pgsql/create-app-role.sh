#!/usr/bin/env bash
#
# Creates a NON-superuser application role that OWNS the schema, so PostgreSQL
# Row-Level Security (declared with FORCE) actually governs the application's
# own queries. Superusers and BYPASSRLS roles are never subject to RLS — so the
# app must never connect as one. The initial superuser (POSTGRES_USER) stays
# available only for explicitly gated, cross-tenant SuperAdmin tasks.
#
# Runs once, on first initialisation of the data volume.
set -e

app_user="${DB_USERNAME:-laradesk}"
app_password="${DB_PASSWORD:-password}"
testing_db="${TESTING_DB:-testing}"

# Role + database ownership (run against the maintenance database).
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE ROLE "${app_user}" WITH LOGIN PASSWORD '${app_password}'
        NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS;
    ALTER DATABASE "${POSTGRES_DB}" OWNER TO "${app_user}";
    CREATE DATABASE "${testing_db}" OWNER "${app_user}";
EOSQL

# Hand the public schema of each database to the app role so it can run migrations.
for db in "$POSTGRES_DB" "$testing_db"; do
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$db" <<-EOSQL
        ALTER SCHEMA public OWNER TO "${app_user}";
        GRANT ALL ON SCHEMA public TO "${app_user}";
EOSQL
done
