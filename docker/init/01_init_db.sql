-- Install task prerequisites
CREATE TABLE users (
   id SERIAL PRIMARY KEY,
   full_name VARCHAR(150) NOT NULL,
   email VARCHAR(100) NOT NULL UNIQUE,
   phone_number VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE loans (
   id SERIAL PRIMARY KEY,
   amount VARCHAR(50) NOT NULL,
   user_id INT NOT NULL REFERENCES users(id)
);

CREATE INDEX idx_loans_user_id ON loans (user_id);