CREATE DATABASE IF NOT EXISTS modular_env_1;
USE modular_env_1;

CREATE TABLE IF NOT EXISTS products(
    id INT(11) AUTO_INCREMENT, 
    name VARCHAR(255), 
    price DECIMAL(10,2),
    PRIMARY KEY (id)
);

INSERT INTO products (name, price) VALUES ('curso front-end', 2500);
INSERT INTO products (name, price) VALUES ('curso front-back', 3500);
INSERT INTO products (name, price) VALUES ('curso fullstack', 4500);
INSERT INTO products (name, price) VALUES ('curso docker', 5500);


GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '123' WITH GRANT OPTION;
FLUSH PRIVILEGES;