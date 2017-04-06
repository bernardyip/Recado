CREATE OR REPLACE FUNCTION remove_expired_tokens()
RETURNS trigger AS
$BODY$
BEGIN
 DELETE FROM public.user_auth_tokens a WHERE a.expires < NOW();
 RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

DROP TRIGGER IF EXISTS remove_expired_tokens_trigger ON public.user_auth_tokens;

CREATE TRIGGER remove_expired_tokens_trigger 
BEFORE INSERT OR UPDATE 
ON public.user_auth_tokens 
FOR EACH STATEMENT
EXECUTE PROCEDURE remove_expired_tokens();