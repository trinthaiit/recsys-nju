BEGIN;

DROP TABLE IF EXISTS page_product;

CREATE TABLE IF NOT EXISTS page_product(
    id bigint(20) PRIMARY KEY AUTO_INCREMENT,
    cookie_id varchar(255) NOT NULL,
    page_id bigint(11) NOT NULL,
    product_id bigint(20) unsigned NOT NULL
);

INSERT INTO page_product (cookie_id, page_id, product_id)
    SELECT f.cookie_id, f.id, p.id
    FROM products p, pageflow f
    WHERE CONCAT('http://www.bagsok.com/product/', p.uri_name, '.html') = f.url;

DROP TABLE IF EXISTS product_product_view_relations;

CREATE TABLE IF NOT EXISTS product_product_view_relations(
    id bigint(20) PRIMARY KEY AUTO_INCREMENT,
    product1 bigint(20) NOT NULL,
    product2 bigint(20) NOT NULL
);

INSERT INTO product_product_view_relations (product1, product2)
    SELECT pp1.product_id, pp2.product_id
    FROM page_product pp1, page_product pp2
    WHERE pp1.cookie_id = pp2.cookie_id AND pp1.product_id != pp2.product_id;

COMMIT;