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
	bid_picked BOOLEAN NOT NULL,
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

INSERT INTO public.category (photo, name, description) VALUES ('everything_else.jpg', 'Everything Else', 'Everything else that does not fit into a given category');
INSERT INTO public.category (photo, name, description) VALUES ('cleaning.jpg', 'Cleaning', 'House cleaning, office cleaning, spring cleaning, etc.');
INSERT INTO public.category (photo, name, description) VALUES ('delivery.jpg', 'Delivery', 'Furniture delivery, food Delivery, fridge moving and removal, etc.');
INSERT INTO public.category (photo, name, description) VALUES ('fixing.jpg', 'Fixing', 'Furniture assembly, appliance repair, TV mounting and installation, etc.');

insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('admin', 'admin', 'admin@recado.com', '99999999', 'Admin', 'I am Admin', '2016-12-28T01:34:44Z', 'admin');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('user', 'user', 'user@recado.com', '00000000', 'Jessica Bennett', 'I am User', '2016-02-09T13:25:44Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('sray2', 'Zow61bKfWwRN', 'sray2@flavors.me', '82793152', 'Scott Ray', null, '2016-12-05T11:15:22Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('hgrant3', 'JOBg2ufU', 'hgrant3@usa.gov', '87781057', 'Howard Grant', null, '2016-04-08T15:08:11Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('ahunt4', '2AGocVNXYX9i', 'ahunt4@deliciousdays.com', '99557790', 'Anna Hunt', null, '2016-07-20T20:06:53Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('rrogers5', 'cXm2ENjtYQDS', 'rrogers5@behance.net', '86618237', 'Russell Rogers', null, '2016-07-02T16:35:23Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('bbanks6', 'unc1DJ4', 'bbanks6@whitehouse.gov', '98850494', 'Bobby Banks', null, '2016-06-08T01:08:39Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('swarren7', '3gepp3', 'swarren7@yahoo.co.jp', '92508849', 'Steven Warren', null, '2016-07-09T06:20:55Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('ckelley8', 'T1loSAuJlEd', 'ckelley8@mashable.com', '99401639', 'Chris Kelley', null, '2016-11-24T01:22:15Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('kfranklin9', 'SunfVZhgD', 'kfranklin9@twitter.com', '98126340', 'Kimberly Franklin', null, '2016-05-08T22:24:48Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('acastilloa', 'IwHyS5q', 'acastilloa@squidoo.com', '91420341', 'Amy Castillo', null, '2016-04-25T19:05:32Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('aharrisb', 'WhslQy', 'aharrisb@gnu.org', '93522649', 'Adam Harris', null, '2016-11-04T09:07:10Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('thunterc', 'U3jKkhHu8jOe', 'thunterc@nbcnews.com', '95614897', 'Terry Hunter', null, '2016-08-15T05:01:26Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('sreedd', 'dM03sXT', 'sreedd@surveymonkey.com', '91777367', 'Sarah Reed', null, '2016-06-09T14:51:47Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('cfrankline', 'FHMu4kYYaF2', 'cfrankline@mozilla.com', '84687007', 'Cynthia Franklin', null, '2016-02-04T22:07:28Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('mhallf', 'J2YzzJPafkX', 'mhallf@psu.edu', '89582702', 'Melissa Hall', null, '2016-03-29T19:44:28Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('rreedg', 'Sz8DL5z44bZw', 'rreedg@livejournal.com', '87811037', 'Robert Reed', null, '2016-06-18T17:36:39Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('polsonh', 'C3TLSZ', 'polsonh@ameblo.jp', '97304619', 'Patricia Olson', null, '2016-10-30T16:39:07Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('twelchi', 'gVv86AWsCoa', 'twelchi@google.pl', '86718105', 'Todd Welch', null, '2016-06-10T00:14:33Z', 'user');
insert into public.user (username, password, email, phone, name, bio, created_time, role) values ('ahawkinsj', 'Zqf8SRwj', 'ahawkinsj@t-online.de', '86719102', 'Amy Hawkins', null, '2016-06-18T12:03:51Z', 'user');

insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Delivery of Parcel', 'Help to deliver a parcel to my aunty house', 276952, '29 Mount Sinai Rise', '2017-12-26T13:03:23Z', '2017-12-26T14:03:23Z', 20, '2017-12-24T13:03:23Z', null, 'pending', 3, 12, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Wash my car', 'Wash my car, simple and easy. Its a green toyota wish', 138944, '5 Westbourne Rd', '2017-10-22T23:43:29Z', '2017-10-23T00:43:29Z', 100, '2017-10-13T23:43:29Z', null, 'pending', 2, 20, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Deliver Groceries', 'I need a dozen eggs, a loaf of whole grain bread, and a stick of butter.', 640456, 'Block 456 456 Jurong West Street 41', '2017-08-21T10:05:23Z', '2017-08-21T12:05:23Z', 10, '2017-08-21T10:05:23Z', null, 'pending', 3, 10, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Pick up luggage', 'Help me pick up my luggage at Changi Airport and deliver to the location stated', 319457, '743 Lor 5 Toa Payoh', '2017-03-27T20:11:17Z', '2017-03-27T22:11:17Z', 30, '2017-03-24T23:11:17Z', null, 'pending', 3, 9, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Mount TV', 'Mount my TV, will contact you for the unit number', 310210, '210 Lor 8 Toa Payoh', '2017-08-28T11:25:20Z', '2017-08-28T16:25:20Z', 20, '2017-08-28T11:25:20Z', null, 'pending', 4, 13, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Furniture Assembly', 'Assemble some IKEA furniture', 540238, '238 Compassvale Walk', '2017-10-02T02:23:24Z', '2017-10-02T09:23:24Z', 20, '2017-10-02T02:23:24Z', null, 'pending', 4, 15, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Manning a booth', 'Man a game booth for an event', 821160, 'Block 160a HDB Punggol', '2017-12-15T10:20:05Z', '2017-12-15T20:20:05Z', 90, '2017-12-08T15:20:05Z', null, 'pending', 1, 20, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Organize office supplies', 'Help to pack up the table so it is more organised',  510770, 'Block 770 770 Pasir Ris Street 71', '2017-03-18T13:36:33Z', '2017-03-18T19:36:33Z', 120, '2017-03-18T13:36:33Z', null, 'pending', 2, 20, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Shoe Cleaning', 'Help with taking stains off my shoe', 510533, '533 Pasir Ris Dr 1', '2017-06-20T03:36:42Z', '2017-06-20T04:36:42Z', 45, '2017-06-17T03:36:42Z', null, 'pending', 2, 10, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Fix table', 'Attach back the table leg', 456375, '23 Roseburn Ave', '2017-05-18T16:36:51Z', '2017-05-18T23:36:51Z', 10, '2017-05-16T16:36:51Z', null, 'pending', 3, 18, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Wash Van', 'Wash my white van located at the spot License Plate is SGX1111', 400010, 'Block 10 10 Eunos Cres', '2017-08-26T15:38:33Z', '2017-08-27T00:38:33Z', 32, '2017-08-25T15:38:33Z', null, 'pending', 2, 2, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Cleaning the house', 'Clean the whole house!', 370067, '67 Circuit Rd', '2017-06-22T07:51:26Z', '2017-06-22T17:51:26Z', 100, '2017-06-14T07:51:26Z', null, 'pending', 2, 6, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Wash my taxi', 'Wash my taxi, yellow citycab', 161054, '54 Havelock Rd', '2017-11-14T01:25:03Z', '2017-11-14T04:25:03Z', 15, '2017-11-13T01:25:03Z', null, 'pending', 2, 20, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Help me with my school work', 'Need help with some math problems, should be easy for university students', 140153, 'Block 153 HDB Mei Ling', '2017-05-17T00:38:29Z', '2017-05-17T06:38:29Z', 10, '2017-05-12T00:38:29Z', null, 'pending', 1, 3, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Buy lunch for me', 'Hungry... Need lunch, will pay back the cost of the lunch', 118425, '27 Prince George''s Park, Block 6', '2017-03-11T10:25:47Z', '2017-03-11T11:25:47Z', 5, '2017-03-08T10:25:47Z', null, 'pending', 3, 10, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Buy dinner for me, offer excludes the food cost', 'Would love to have western for dinner!', 129538, '25 Faber Ave', '2017-10-18T21:00:01Z', '2017-10-19T01:00:01Z', 7, '2017-10-13T21:00:01Z', null, 'pending', 3, 1, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Move some furniture', 'Last few furniture to move, moving company bailed :(', 609957, '30 Boon Lay Way', '2017-09-21T15:19:37Z', '2017-09-22T01:19:37Z', 80, '2017-09-15T15:19:37Z', null, 'pending', 3, 10, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Babysit my child', 'Quick 2 hour baby sit, fast cash', 640493, '493 Jurong West Street 41', '2017-12-09T15:32:39Z', '2017-12-09T17:32:39Z', 30, '2017-12-07T00:32:39Z', null, 'pending', 1, 17, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Buy me some cables', 'Need some lighting USB cables fast!', 640815, 'Block 815 HDB Jurong West', '2017-03-15T20:10:30Z', '2017-03-15T20:40:30Z', 10, '2017-03-15T19:16:30Z', null, 'pending', 3, 20, 'f');
insert into public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, category_id, creator_id, bid_picked) values ('Pack up my room', 'Pack my room, unit number will be given via personal contact', 628973, '9 Joo Koon Rd', '2017-05-29T22:31:00Z', '2017-05-30T02:31:00Z', 50, '2017-05-26T22:31:00Z', null, 'pending', 2, 4, 'f');

insert into public.bid (amount, bid_time, selected, user_id, task_id) values (9, '2017-08-18T10:36:46Z', false, 5, 3);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (10, '2016-05-07T20:16:36Z', false, 4, 14);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (20, '2017-12-20T16:58:12Z', false, 18, 1);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (95, '2016-06-12T04:42:31Z', false, 3, 12);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (120, '2016-03-10T18:03:05Z', false, 17, 8);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (47.25, '2017-05-20T14:59:54Z', false, 18, 20);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (40, '2017-06-20T10:05:03Z', false, 12, 9);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (80, '2016-05-8T16:38:52Z', false, 16, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (80, '2017-09-12T03:12:02Z', false, 5, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (25, '2017-12-20T13:29:55Z', false, 6, 1);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (20, '2016-08-24T03:55:35Z', false, 5, 5);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (80, '2016-09-11T20:11:27Z', false, 8, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (30, '2017-12-03T04:53:09Z', false, 16, 18);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (15, '2017-11-09T04:32:18Z', false, 14, 13);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (90, '2016-12-04T19:11:24Z', false, 16, 7);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (99, '2017-10-12T17:03:48Z', false, 14, 2);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (100, '2017-06-13T04:18:50Z', false, 13, 12);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (43, '2016-06-15T08:38:12Z', false, 19, 9);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (40, '2016-12-02T02:58:23Z', false, 16, 9);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (10, '2016-05-16T23:03:28Z', false, 2, 10);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (43, '2016-06-13T18:41:40Z', false, 12, 9);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (30, '2017-08-22T07:31:54Z', false, 9, 11);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (50, '2016-05-26T08:47:49Z', false, 7, 20);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (50, '2017-05-24T19:15:09Z', false, 17, 20);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (30, '2016-12-06T00:16:55Z', false, 7, 18);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (120, '2017-03-16T05:20:29Z', false, 8, 8);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (85, '2016-10-12T18:47:04Z', false, 5, 2);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (8, '2017-10-11T23:13:20Z', false, 10, 16);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (100, '2016-10-12T15:44:15Z', false, 18, 2);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (80, '2016-09-10T08:03:04Z', false, 4, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (100, '2016-06-09T10:07:29Z', false, 13, 12);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (47, '2017-05-20T22:25:25Z', false, 14, 20);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (5, '2016-03-06T14:06:02Z', false, 3, 15);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (20, '2016-08-25T02:29:24Z', false, 12, 5);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (10, '2016-05-16T22:26:50Z', false, 16, 10);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (20, '2017-10-01T02:04:04Z', false, 2, 6);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (80, '2017-09-11T01:03:40Z', false, 11, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (70, '2016-09-14T08:22:18Z', false, 15, 17);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (7, '2017-05-15T20:41:26Z', false, 4, 10);
insert into public.bid (amount, bid_time, selected, user_id, task_id) values (38, '2016-06-09T04:50:24Z', false, 19, 9);

insert into public.comment (comment, created_time, user_id, task_id) values ('Anything else?', '2017-08-22T15:05:39Z', 20, 3);
insert into public.comment (comment, created_time, user_id, task_id) values ('How messy is your room?', '2017-05-28T03:58:26Z', 10, 20);
insert into public.comment (comment, created_time, user_id, task_id) values ('Do you have nails and hammer?', '2017-05-18T09:02:43Z', 1, 10);
insert into public.comment (comment, created_time, user_id, task_id) values ('Is a vehicle provided?', '2017-09-16T21:08:19Z', 16, 17);
insert into public.comment (comment, created_time, user_id, task_id) values ('How big is the house?', '2017-06-15T08:10:13Z', 18, 12);
insert into public.comment (comment, created_time, user_id, task_id) values ('How large is the parcel?', '2016-12-25T12:13:27Z', 8, 1);
insert into public.comment (comment, created_time, user_id, task_id) values ('Any preferred marts?', '2017-08-22T08:21:58Z', 19, 3);
insert into public.comment (comment, created_time, user_id, task_id) values ('Are tools provided?', '2017-05-17T16:41:31Z', 7, 10);
insert into public.comment (comment, created_time, user_id, task_id) values ('What kind of TV is it? Do you have a mount?', '2017-08-29T19:15:48Z', 15, 5);
insert into public.comment (comment, created_time, user_id, task_id) values ( 'No, you will need a truck to help with the moving.', '2016-09-17T08:17:56Z', 10, 17);
