
CREATE DATABASE IF NOT EXISTS soda_beach_resort
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE soda_beach_resort;

CREATE TABLE CUSTOMER (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE STAFF (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role VARCHAR(30) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE DAY_PASS_CATEGORY (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT
);

CREATE TABLE COTTAGE (
    cottage_id INT AUTO_INCREMENT PRIMARY KEY,
    cottage_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    rental_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) NOT NULL DEFAULT 'Available',
    CHECK (capacity > 0),
    CHECK (rental_rate >= 0)
);

CREATE TABLE BOOKING (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    cottage_id INT NULL,
    category_id INT NULL,
    booking_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    visit_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    booking_status VARCHAR(20) NOT NULL DEFAULT 'Pending',

    CONSTRAINT fk_booking_customer
        FOREIGN KEY (customer_id)
        REFERENCES CUSTOMER(customer_id),

    CONSTRAINT fk_booking_cottage
        FOREIGN KEY (cottage_id)
        REFERENCES COTTAGE(cottage_id)
        ON DELETE SET NULL,

    CONSTRAINT fk_booking_category
        FOREIGN KEY (category_id)
        REFERENCES DAY_PASS_CATEGORY(category_id)
        ON DELETE SET NULL,

    CHECK (total_amount >= 0)
);

CREATE TABLE PAYMENT (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'Failed',

    CONSTRAINT fk_payment_booking
        FOREIGN KEY (booking_id)
        REFERENCES BOOKING(booking_id),

    CHECK (amount_paid >= 0)
);

CREATE TABLE CHECKIN_LOG (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    staff_id INT NOT NULL,
    qr_code_hash VARCHAR(255) NOT NULL,
    checkin_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    entry_status VARCHAR(20) NOT NULL DEFAULT 'Valid',

    CONSTRAINT fk_checkin_booking
        FOREIGN KEY (booking_id)
        REFERENCES BOOKING(booking_id),

    CONSTRAINT fk_checkin_staff
        FOREIGN KEY (staff_id)
        REFERENCES STAFF(staff_id)
);

-- Sample day-pass categories: change these to actual resort rates.
INSERT INTO DAY_PASS_CATEGORY
    (category_name, price, description)
VALUES
    ('Adult', 150.00, 'Sample adult day-pass rate'),
    ('Child', 100.00, 'Sample child day-pass rate'),
    ('Senior', 120.00, 'Sample senior day-pass rate');

-- Sample cottages: replace with actual cottage information.
INSERT INTO COTTAGE
    (cottage_number, capacity, rental_rate, status)
VALUES
    ('Cottage 1', 6, 500.00, 'Available'),
    ('Cottage 2', 10, 800.00, 'Available'),
    ('Cottage 3', 15, 1200.00, 'Available');
