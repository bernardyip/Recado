--Remove all existing tables if exists
DROP TABLE IF EXISTS public.user CASCADE;
DROP TABLE IF EXISTS public.category CASCADE;
DROP TABLE IF EXISTS public.task CASCADE;
DROP TABLE IF EXISTS public.bid CASCADE;
DROP TABLE IF EXISTS public.comment CASCADE;

--Create the tables with constraints
CREATE TABLE public.category (
	id SERIAL PRIMARY KEY,
	photo CHARACTER(128) NOT NULL,
	name CHARACTER(32) NOT NULL,
	description CHARACTER(256) NOT NULL
);

CREATE TABLE public.user (
	id SERIAL PRIMARY KEY,
	username CHARACTER(32) UNIQUE NOT NULL,
	password CHARACTER(128) NOT NULL,
	email CHARACTER(128),
	phone CHARACTER(8),
	name CHARACTER(64) NOT NULL,
	bio CHARACTER(1000),
	created_time TIMESTAMP WITH TIME ZONE NOT NULL,
	last_logged_in TIMESTAMP WITH TIME ZONE,
	role CHARACTER(32) NOT NULL,
	CHECK(role = 'admin' OR role='user')
);

CREATE TABLE public.task (
	id SERIAL PRIMARY KEY,
	name CHARACTER(128) NOT NULL,
	description CHARACTER(1024) NOT NULL,
	postal_code INTEGER NOT NULL,
	location CHARACTER(128) NOT NULL,
	task_start_time TIMESTAMP WITH TIME ZONE NOT NULL,
	task_end_time TIMESTAMP WITH TIME ZONE NOT NULL,
	listing_price MONEY,
	created_time TIMESTAMP WITH TIME ZONE NOT NULL,
	updated_time TIMESTAMP WITH TIME ZONE,
	status CHARACTER(32) NOT NULL,
	bid_picked INTEGER REFERENCES public.bid(id) ON DELETE CASCADE UNIQUE,
	category_id INTEGER REFERENCES public.category(id) ON DELETE CASCADE NOT NULL,
	creator_id INTEGER REFERENCES public.user(id) ON DELETE CASCADE NOT NULL,
	CHECK(status = 'pending' OR status='completed'),
	CHECK (task_end_time > task_start_time),
	CHECK (updated_time > created_time)
);

CREATE TABLE public.bid (
	id SERIAL PRIMARY KEY,
	amount MONEY NOT NULL,
	bid_time TIMESTAMP WITH TIME ZONE NOT NULL,
	selected BOOLEAN NOT NULL DEFAULT FALSE,
	user_id INTEGER REFERENCES public.user(id) ON DELETE CASCADE NOT NULL,
	task_id INTEGER REFERENCES public.task(id) ON DELETE CASCADE NOT NULL
);

CREATE TABLE public.comment (
	id SERIAL PRIMARY KEY,
	comment CHARACTER(128) NOT NULL,
	created_time TIMESTAMP NOT NULL,
	user_id INTEGER REFERENCES public.user(id) ON DELETE CASCADE NOT NULL,
	task_id INTEGER REFERENCES public.task(id) ON DELETE CASCADE NOT NULL
);

