
 
SET NAMES utf8mb4; 
SET foreign_key_checks = 0; 
 

CREATE TABLE IF NOT EXISTS products ( 
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT, 
    name        VARCHAR(255)    NOT NULL, 
    unit        VARCHAR(50)     NOT NULL                COMMENT 'Unit of measure, e.g. bag, ton, m2, piece', 
    description TEXT            NULL, 
 
    PRIMARY KEY (id), 
    UNIQUE KEY uq_products_name (name) 
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Canonical product catalogue'; 
 

CREATE TABLE IF NOT EXISTS suppliers ( 
    id      INT UNSIGNED    NOT NULL AUTO_INCREMENT, 
    name    VARCHAR(255)    NOT NULL, 
    phone   VARCHAR(50)     NOT NULL, 
    address VARCHAR(500)    NULL, 
 
    PRIMARY KEY (id) 
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Supplier master records'; 
 

CREATE TABLE IF NOT EXISTS customers ( 
    id      INT UNSIGNED    NOT NULL AUTO_INCREMENT, 
    name    VARCHAR(255)    NOT NULL, 
    phone   VARCHAR(50)     NOT NULL, 
    address VARCHAR(500)    NULL, 
 
    PRIMARY KEY (id) 
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Customer/trader master records'; 
 

CREATE TABLE IF NOT EXISTS product_suppliers ( 
    product_id  INT UNSIGNED        NOT NULL, 
    supplier_id INT UNSIGNED        NOT NULL, 
    unit_price  DECIMAL(12, 2)      NOT NULL, 
 
    PRIMARY KEY (product_id, supplier_id), 
 
    CONSTRAINT fk_ps_product 
        FOREIGN KEY (product_id) 
        REFERENCES products (id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE, 
 
    CONSTRAINT fk_ps_supplier 
        FOREIGN KEY (supplier_id) 
        REFERENCES suppliers (id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE 
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Supplier-specific price per product; resolves the many-to-many relationship'; 
 

CREATE TABLE IF NOT EXISTS stock_movements ( 
    id          INT UNSIGNED                    NOT NULL AUTO_INCREMENT, 
    product_id  INT UNSIGNED                    NOT NULL, 
    type        ENUM('supply', 'sale')          NOT NULL, 
    quantity    DECIMAL(12, 3)                  NOT NULL    COMMENT 'Always positive; direction implied by type', 
    unit_price  DECIMAL(12, 2)                  NOT NULL    COMMENT 'Historical transaction price at the time of movement', 
    supplier_id INT UNSIGNED                    NULL        COMMENT 'Set for supply movements; NULL for sales [PHP enforced]', 
    customer_id INT UNSIGNED                    NULL        COMMENT 'Set for sale movements; NULL for supplies [PHP enforced]', 
    created_at  TIMESTAMP                       NOT NULL DEFAULT CURRENT_TIMESTAMP, 
 
    PRIMARY KEY (id), 
 
    -- Quantity must be positive (MySQL 8.0.16+ supports CHECK constraints) 
    CONSTRAINT chk_sm_quantity_positive CHECK (quantity > 0), 
 
    CONSTRAINT fk_sm_product 
        FOREIGN KEY (product_id) 
        REFERENCES products (id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE, 
 
    CONSTRAINT fk_sm_supplier 
        FOREIGN KEY (supplier_id) 
        REFERENCES suppliers (id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE, 
 
    CONSTRAINT fk_sm_customer 
        FOREIGN KEY (customer_id) 
        REFERENCES customers (id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE, 
 
    INDEX idx_sm_product_type    (product_id, type), 
    INDEX idx_sm_supplier        (supplier_id), 
    INDEX idx_sm_customer        (customer_id), 
    INDEX idx_sm_created_at      (created_at) 
 
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci 
  COMMENT='Immutable log of all stock movements (supplies and sales)'; 
 
SET foreign_key_checks = 1; 
