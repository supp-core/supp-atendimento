--
-- PostgreSQL database dump
--

-- Dumped from database version 15.12
-- Dumped by pg_dump version 15.12

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: attendant; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.attendant (
    id integer NOT NULL,
    sector_id integer,
    user_id integer NOT NULL,
    name character varying(100) NOT NULL,
    status character varying(30) NOT NULL,
    function character varying(100) NOT NULL
);


ALTER TABLE public.attendant OWNER TO suppadmin;

--
-- Name: attendant_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.attendant_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.attendant_id_seq OWNER TO suppadmin;

--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO suppadmin;

--
-- Name: sector; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.sector (
    id integer NOT NULL,
    name character varying(20) NOT NULL
);


ALTER TABLE public.sector OWNER TO suppadmin;

--
-- Name: sector_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.sector_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sector_id_seq OWNER TO suppadmin;

--
-- Name: service; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.service (
    id integer NOT NULL,
    sector_id integer NOT NULL,
    requester_id integer,
    reponsible_id integer,
    created_by_admin_id integer,
    title character varying(100) NOT NULL,
    description text NOT NULL,
    status character varying(30) NOT NULL,
    priority character varying(20) DEFAULT 'NORMAL'::character varying NOT NULL,
    date_create timestamp(0) without time zone NOT NULL,
    date_update timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    date_conclusion timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    created_by_admin boolean DEFAULT false NOT NULL
);


ALTER TABLE public.service OWNER TO suppadmin;

--
-- Name: service_attachment; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.service_attachment (
    id integer NOT NULL,
    service_id integer NOT NULL,
    filename character varying(255) NOT NULL,
    original_filename character varying(255) NOT NULL,
    mime_type character varying(50) NOT NULL,
    file_size integer NOT NULL
);


ALTER TABLE public.service_attachment OWNER TO suppadmin;

--
-- Name: service_attachment_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.service_attachment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.service_attachment_id_seq OWNER TO suppadmin;

--
-- Name: service_history; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public.service_history (
    id integer NOT NULL,
    service_id integer NOT NULL,
    responsible_id integer,
    comment text,
    date_history timestamp(0) without time zone NOT NULL,
    status_prev character varying(30) NOT NULL,
    status_post character varying(30) DEFAULT NULL::character varying
);


ALTER TABLE public.service_history OWNER TO suppadmin;

--
-- Name: service_history_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.service_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.service_history_id_seq OWNER TO suppadmin;

--
-- Name: service_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.service_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.service_id_seq OWNER TO suppadmin;

--
-- Name: user; Type: TABLE; Schema: public; Owner: suppadmin
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    roles json NOT NULL,
    is_attendant boolean DEFAULT false NOT NULL
);


ALTER TABLE public."user" OWNER TO suppadmin;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: suppadmin
--

CREATE SEQUENCE public.user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO suppadmin;

--
-- Data for Name: attendant; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.attendant (id, sector_id, user_id, name, status, function) FROM stdin;
3	11	3	Rafael Assumpcao de Oliveira	AVAILABLE	Admin
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20250326134823	2025-03-31 13:42:13	2357
\.


--
-- Data for Name: sector; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.sector (id, name) FROM stdin;
9	Infra
10	Dev
11	Admin
12	DevOps
\.


--
-- Data for Name: service; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.service (id, sector_id, requester_id, reponsible_id, created_by_admin_id, title, description, status, priority, date_create, date_update, date_conclusion, created_by_admin) FROM stdin;
\.


--
-- Data for Name: service_attachment; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.service_attachment (id, service_id, filename, original_filename, mime_type, file_size) FROM stdin;
\.


--
-- Data for Name: service_history; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public.service_history (id, service_id, responsible_id, comment, date_history, status_prev, status_post) FROM stdin;
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: suppadmin
--

COPY public."user" (id, name, email, password, roles, is_attendant) FROM stdin;
3	Rafael Assumpcao de Oliveira	rafael.assumpcao@pbh.gov.br	$2y$13$FLMflo9QERq7K/Aq8s8VXeByDbKzPRM3EjMZkn1E1ZmWPBL56BIcy	["ROLE_USER","ROLE_ATTENDANT","ROLE_ADMIN"]	t
\.


--
-- Name: attendant_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.attendant_id_seq', 3, true);


--
-- Name: sector_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.sector_id_seq', 12, true);


--
-- Name: service_attachment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.service_attachment_id_seq', 1, false);


--
-- Name: service_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.service_history_id_seq', 1, false);


--
-- Name: service_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.service_id_seq', 1, false);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: suppadmin
--

SELECT pg_catalog.setval('public.user_id_seq', 3, true);


--
-- Name: attendant attendant_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.attendant
    ADD CONSTRAINT attendant_pkey PRIMARY KEY (id);


--
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: sector sector_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.sector
    ADD CONSTRAINT sector_pkey PRIMARY KEY (id);


--
-- Name: service_attachment service_attachment_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service_attachment
    ADD CONSTRAINT service_attachment_pkey PRIMARY KEY (id);


--
-- Name: service_history service_history_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service_history
    ADD CONSTRAINT service_history_pkey PRIMARY KEY (id);


--
-- Name: service service_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT service_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: idx_b2508f91de95c867; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_b2508f91de95c867 ON public.attendant USING btree (sector_id);


--
-- Name: idx_e19d9ad264f1f4ee; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e19d9ad264f1f4ee ON public.service USING btree (created_by_admin_id);


--
-- Name: idx_e19d9ad29fb57326; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e19d9ad29fb57326 ON public.service USING btree (reponsible_id);


--
-- Name: idx_e19d9ad2de95c867; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e19d9ad2de95c867 ON public.service USING btree (sector_id);


--
-- Name: idx_e19d9ad2ed442cf4; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e19d9ad2ed442cf4 ON public.service USING btree (requester_id);


--
-- Name: idx_e83e22d7602ad315; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e83e22d7602ad315 ON public.service_history USING btree (responsible_id);


--
-- Name: idx_e83e22d7ed5ca9e6; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_e83e22d7ed5ca9e6 ON public.service_history USING btree (service_id);


--
-- Name: idx_ef0ee00fed5ca9e6; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE INDEX idx_ef0ee00fed5ca9e6 ON public.service_attachment USING btree (service_id);


--
-- Name: uniq_8d93d649e7927c74; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON public."user" USING btree (email);


--
-- Name: uniq_b2508f91a76ed395; Type: INDEX; Schema: public; Owner: suppadmin
--

CREATE UNIQUE INDEX uniq_b2508f91a76ed395 ON public.attendant USING btree (user_id);


--
-- Name: attendant fk_b2508f91a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.attendant
    ADD CONSTRAINT fk_b2508f91a76ed395 FOREIGN KEY (user_id) REFERENCES public."user"(id);


--
-- Name: attendant fk_b2508f91de95c867; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.attendant
    ADD CONSTRAINT fk_b2508f91de95c867 FOREIGN KEY (sector_id) REFERENCES public.sector(id);


--
-- Name: service fk_e19d9ad264f1f4ee; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_e19d9ad264f1f4ee FOREIGN KEY (created_by_admin_id) REFERENCES public.attendant(id);


--
-- Name: service fk_e19d9ad29fb57326; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_e19d9ad29fb57326 FOREIGN KEY (reponsible_id) REFERENCES public.attendant(id);


--
-- Name: service fk_e19d9ad2de95c867; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_e19d9ad2de95c867 FOREIGN KEY (sector_id) REFERENCES public.sector(id);


--
-- Name: service fk_e19d9ad2ed442cf4; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_e19d9ad2ed442cf4 FOREIGN KEY (requester_id) REFERENCES public."user"(id);


--
-- Name: service_history fk_e83e22d7602ad315; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service_history
    ADD CONSTRAINT fk_e83e22d7602ad315 FOREIGN KEY (responsible_id) REFERENCES public.attendant(id);


--
-- Name: service_history fk_e83e22d7ed5ca9e6; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service_history
    ADD CONSTRAINT fk_e83e22d7ed5ca9e6 FOREIGN KEY (service_id) REFERENCES public.service(id);


--
-- Name: service_attachment fk_ef0ee00fed5ca9e6; Type: FK CONSTRAINT; Schema: public; Owner: suppadmin
--

ALTER TABLE ONLY public.service_attachment
    ADD CONSTRAINT fk_ef0ee00fed5ca9e6 FOREIGN KEY (service_id) REFERENCES public.service(id);


--
-- PostgreSQL database dump complete
--

