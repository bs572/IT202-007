ALTER TABLE Products
    ADD COLUMN visibility tinyint default 0,
    ADD COLUMN category varchar(30) default '';