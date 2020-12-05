CREATE TABLE IF NOT EXISTS `OrderItems`
(
    `id`         int auto_increment not null,
    `product_id`    int,
    `order_id` int,
    `created`    timestamp default current_timestamp,
    `quantity` int,
    `unit_price` decimal(12, 2),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_id`) REFERENCES Users(`id`),
    FOREIGN KEY (`order_id`) REFERENCES Orders(`id`)
)