
CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: parcel; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parcel (
                               id integer NOT NULL,
                               cuzk_gml_id character varying DEFAULT 255 NOT NULL,
                               national_cadastral_reference text,
                               ku_code character varying DEFAULT 255,
                               ku_name character varying DEFAULT 255,
                               parcel_number character varying DEFAULT 255,
                               area_m2 numeric,
                               geom public.geometry(MultiPolygon,5514) NOT NULL
);


ALTER TABLE public.parcel OWNER TO postgres;

--
-- Name: parcel_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parcel_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.parcel_id_seq OWNER TO postgres;

--
-- Name: parcel_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parcel_id_seq OWNED BY public.parcel.id;


--
-- Name: sync_date; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sync_date (
                                  cached timestamp without time zone NOT NULL
);


ALTER TABLE public.sync_date OWNER TO postgres;

--
-- Name: parcel id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcel ALTER COLUMN id SET DEFAULT nextval('public.parcel_id_seq'::regclass);


ALTER TABLE ONLY public.parcel
    ADD CONSTRAINT parcel_pkey PRIMARY KEY (id);


--
-- Name: parcel uq_cuzk_gml; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcel
    ADD CONSTRAINT uq_cuzk_gml UNIQUE (cuzk_gml_id);


--
-- Name: parcels_geom_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX parcels_geom_idx ON public.parcel USING gist (geom);


--
-- Name: parcels_gml_id_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX parcels_gml_id_idx ON public.parcel USING btree (cuzk_gml_id);


--
-- Name: parcels_ku_code_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX parcels_ku_code_idx ON public.parcel USING btree (ku_code);


--
-- Name: parcels_parcel_number_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX parcels_parcel_number_idx ON public.parcel USING btree (parcel_number);

