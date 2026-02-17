# Database schema for the project

## Table: users

### Store the user information


| Column Name | Data Type   | Description                      |
| ----------- | ----------- | -------------------------------- |
| id          | SERIAL      | Primary Key                      |
|             |             |                                  |
| password    | VARCHAR(50) | Not Null                         |
| email       | VARCHAR(50) | Not Null, Unique                 |
| acvatar     | VARCHAR(50) | Not Null, Default: 'default.jpg' |

| created_on  | TIMESTAMP  | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP  | Not Null, Default: CURRENT_TIMESTAMP |

### Table: addresses

#### Store the address information of the user to deliver the products


| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | Data Type   | Primary Key                          |
| user_id     | INTEGER     | Foreign Key(users.id)                |
| line1       | TEXT        | Not Null                             |
| line2       | TEXT        | Not Null                             |
| city        | VARCHAR(50) | Not Null                             |
| state       | VARCHAR(50) | Not Null                             |
| country     | VARCHAR(50) | Not Null                             |
| postal_code | VARCHAR(50) | Not Null                             |
| longitude   | FLOAT       | Not Null                             |
| latitude    | FLOAT       | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: categories

#### Store the categories of the products


| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| name        | VARCHAR(50) | Not Null, Unique                     |
| description | TEXT        | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: products

#### Store the products information


| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| category_id | INTEGER     | Foreign Key(categories.id)           |
| name        | VARCHAR(50) | Not Null                             |
| description | TEXT        | Not Null                             |
| price       | FLOAT       | Not Null                             |
| image       | VARCHAR(50) | Not Null, Default: 'default.jpg      |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: orders

#### Store the order information


| Column Name  | Data Type   | Description                          |
| ------------ | ----------- | ------------------------------------ |
| id           | SERIAL      | Primary Key                          |
| user_id      | INTEGER     | Foreign Key(users.id)                |
| address_id   | INTEGER     | Foreign Key(addresses.id)            |
| status       | VARCHAR(50) | Not Null, Default: 'PENDING'         |
| total_amount | FLOAT       | Not Null                             |
| cart_id      | INTEGER     | Foreign Key(carts.id)                |
| created_on   | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on   | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Order: order_items


| Column Name | Data Type | Description                          |
| ----------- | --------- | ------------------------------------ |
| id          | SERIAL    | Primary Key                          |
| order_id    | INTEGER   | Foreign Key(orders.id)               |
| product_id  | INTEGER   | Foreign Key(products.id)             |
| quantity    | INTEGER   | Not Null                             |
| price       | FLOAT     | Not Null                             |
| created_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Table: carts


| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| user_id     | INTEGER     | Foreign Key(users.id)                |
| status      | VARCHAR(50) | Not Null, Default: 'ACTIVE'          |
| total       | FLOAT       | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: cart_items


| Column Name | Data Type | Description                          |
| ----------- | --------- | ------------------------------------ |
| id          | SERIAL    | Primary Key                          |
| cart_id     | INTEGER   | Foreign Key(carts.id)                |
| product_id  | INTEGER   | Foreign Key(products.id)             |
| quantity    | INTEGER   | Not Null                             |
| price       | FLOAT     | Not Null                             |
| created_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Table: Payments
| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| order_id    | INTEGER     | Foreign Key(orders.id)               |
| user_id     | INTEGER     | Foreign Key(users.id)                |
| amount      | FLOAT       | Not Null                             |
| status      | VARCHAR(50) | Not Null, Default: 'PENDING'         |
| payment_method      | VARCHAR(50) | Not Null, Default: 'CREDIT_CARD' |
| payment_date| TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |