drop database if exists hotel_booking;
create database hotel_booking;
use hotel_booking;

drop table if exists users;
create table users(
user_id int auto_increment primary key,
fullname varchar(255) not null,
email varchar(255) not null unique,
password varchar(255) not null,
role varchar(20) not null default 'customer',
constraint chk_role check (role in ('customer','staff','admin'))
);

drop table if exists hotels_page;
create table hotels_page(
hotel_id int auto_increment primary key,
hotel_name varchar(100) not null,
location varchar(250) not null,
description text,
price decimal(10,2),
rating decimal(2,1),
image_url VARCHAR(255) NOT NULL
);

drop table if exists rooms;
create table rooms(
room_id int auto_increment primary key,
hotel_id int not null,
room_number varchar(20) not null,
room_type varchar(50) not null,
bed_type varchar(225) not null,
price_per_night decimal(10,2),
status varchar(50) not null default 'available',
image_url VARCHAR(225) NOT NULL,
foreign key (hotel_id) references hotels_page(hotel_id)
);

drop table if exists bookings;
create table bookings(
booking_id int auto_increment primary key,
user_id int not null,
hotel_id int not null,
room_id int not null,
check_in_date date,
check_out_date date,
total_price decimal(10,2),
booking_status varchar(100) not null default 'pending',
foreign key (user_id) references users(user_id),
foreign key (hotel_id) references hotels_page(hotel_id),
foreign key (room_id) references rooms(room_id)
);

drop table if exists reviews;
create table reviews(
review_id int auto_increment primary key,
user_id int not null,
hotel_id int not null,
room_id int not null,
rating int not null,
comment text not null,
created_at datetime default current_timestamp,
constraint chk_rating check(rating between 1 and 5 ),
foreign key (user_id) references users(user_id),
foreign key (hotel_id) references hotels_page(hotel_id),
foreign key (room_id) references rooms(room_id)
);

insert into users(fullname,email,password,role)values
('Admin User','admin@example.com','$2y$12$vDD9CdtB4qSWvau/HEkyvemwhypxKAwgyMgeU5R6gNC6lr2w31MLS','admin'),
('Staff User','staff@example.com','$2y$12$vDD9CdtB4qSWvau/HEkyvemwhypxKAwgyMgeU5R6gNC6lr2w31MLS','staff'),
('Customer User','customer@example','$2y$12$vDD9CdtB4qSWvau/HEkyvemwhypxKAwgyMgeU5R6gNC6lr2w31MLS','customer');


use hotel_booking;

insert into hotels_page(hotel_name,location,description,price,rating,image_url)values
('Eastern & Oriental Hotel','George Town,Penang','A historic luxury hotel in George Town',680.00,4.5, 'https://images.openai.com/static-rsc-4/wBiRc4r70P1fMBPWsaUZ0cEAH437R-zbfLewxuRvnNapHn5ZpB4JtJcyCuB2ocldEycyOZZftDJsG2Iwl5z4lBLIm5TFRnD3FvyFN8oHrnojnZUla2X3A8ClHUmEkwJQ3pOkcNsNwRE2u40YR6qui2zWfy0HMAysb_rLsW7-clYpSW8QbLBkNbdeEdBwrb6r?purpose=fullsize'),
('Shangri-La Rasa Sayang','Batu Ferringhi,Penang','A luxury resort near the beach',600.00,4.6,'https://images.openai.com/static-rsc-4/sKNjeA9EfMvBbqdeKaJbsOhgTYiFPYTyNhSjHDh4xaN_8A5NCDRjT4raTzYsXsHU_iidOfu97M8GuhAR7JPO8LCd51AnmBZw9XQ3jBZ3MOmdRHFt81XjrpMk63xoUdvLSe0q4ge9hJ-ptNgH0kIuFfRnyJRa3x-IKSMv7lsnoxB397eDar6KtmJLrVdQHlii?purpose=fullsize'),
('Royale Chulan Penang','George Town,Penang','A heritage-style hotel located near the waterfront in George Town.',280.00,4.1,'https://media-cdn.tripadvisor.com/media/photo-s/0e/c3/03/ba/hotel-facade.jpg'),
('St.Giles Wembley Penang Hotel','George Town,Penang','A modern hotel located in the heart of George Town.',300.00,4.3,'https://images.openai.com/static-rsc-4/o33RS20vqgp9cGbMxcyz2pVwWWyszmQ5fRP-soxCCSLdMrvhni7NnUnRXcfJtuwaaYdOhrm58qj5afQ_mjc4PwxhHPwVhTtPs7pGMRSs0ZBEWW1cB5zkWHkYV_4aS9wn9NrakjVvNCYdpKduW4t3n_aUH1VYXN0hfehF60uEohq-i990OpWeNnoHSbr71JoL?purpose=fullsize'),
('Amari Johor Bahru','Johor Bahru City Centre','A modern hotel located in the heart of Johor Bahru.',320.00,4.4,'https://images.trvl-media.com/hotels/18000000/17070000/17069300/17069266/48b54769.jpg?impolicy=resizecrop&rw=1200&ra=fit'),
('St. Giles Southkey Johor Bahru','Southkey,Johor Bahru','A modern hotel located at Mid Valley Southkey.',450.00,4.1,'https://images.openai.com/static-rsc-4/BvrVI_8NO6XgEiKQcwBaKD_F7yPaTO3Asw8DBoh2pa6coXpjlMTqPvB1dP3WQC6lLMEkaXzQ67tuU53PNAK7MH-kJ05loyk0oE9Hr17WXzERncOBvOt2MX51guCYB1cyG-oLNWfYVcPJiLguXKuu1qfck8_cFFM8Vvn1NATDovO0FaoAA1FK-cnT1rw8EbGx?purpose=fullsize'),
('Renaissance Johor Bahru Hotel','Permas Jaya,Johor Bahru','A stylish hotel offering comfortable rooms and dining.',480.00,4.4,'https://images.openai.com/static-rsc-4/Lk1LwKphPm9XFcRmn3IHRgKZKqobDzbPBN-RjRllINp49DVT6TowhtYp5y3DprYSKAEEoFUX_UVoVW25NGGPO4mJNTdeFiUxR6Oz8VMGoZmByvankU4qaj5FV8JIu6fenyJIXiDPqotQ3EGcpJLr7jwEQxJ08URaP1_VgAtBQdrVnxt8fJOXaHPCA_TjSvAw?purpose=fullsize'),
('Holiday Villa Johor Bahru City Centre','Taman Abad, Johor Bahru','A comfortable city-centre hotel near KSL City Mall.',230.00,4.2,'https://a0.muscache.com/im/pictures/miso/Hosting-808139756049571207/original/bbe2584d-992b-427e-a02c-a363c3197691.jpeg'),
('Hatten Hotel Melaka','Bandar Hilir,Melaka','A modern hotel located near the historical attractions of Melaka.',280.00,4.1,'https://images.openai.com/static-rsc-4/UXvucdowQNtsbL9kCw9cjUVFGYE7lJmD4ZkgViIoK0Z5CroRG_NEddnhI0s6-wz_BEsEsa1J2xIWPUPlXOWDAicBJJzHwqOcD25nUBEw1EM36w9zM4oc0O1L7yVFXSDu-3pWm5mEShcFfoU28-Y0ssAB6YnQtKOb174dSExpnLzTWcad9iVLugUVDOEz0D-F?purpose=fullsize'),
('Swiss Garden Hotel Melaka','Bunga Raya,Melaka','A comfortable hotel located near Melaka city centre and popular attractions.',200.00,4.1,'https://melaka.96network.com/wp-content/uploads/2022/09/Swiss-Garden-Hotel-Melaka.jpg'),
('Holiday Inn Melaka','Jalan Syed Abdul Aziz,Melaka','A modern hotel offering comfortable rooms with views of the Melaka Strait.',350.00,4.2,'https://images.openai.com/static-rsc-4/rxVbpJhMTW3iNWuk0eyGz85d4JkZy-fa35MK5byl9AFss7lBZlySIzsa8anUhWANbYhdsl9DjO2JixzX0kQuuxPNDJsM4DfwlLWmf7S44ANU7owrDloUdSQQKUFxVG6yjdA6ThFAt3QJ92LeeibldRmoB9wSEkANaThvjcLIEqfwuyvxgKay5-Dk4SS7T99u?purpose=fullsize'),
('Hyatt Regency Kuantan Resort','Teluk Cempedak,Kuantan','A beachfront resort offering comfortable rooms and beautiful sea views in Kuantan.',450.00,4.3,'https://images.openai.com/static-rsc-4/Rzpbyuhg8tSDf1mlM7J2-URQzw1KS1zUU6rugyojRIrFW8kmG5Wd6rGqMamHFXwUTTv06DSr1-hgfrkZqqTTnKyD50WJkA1-GEFVC9vv7Ii4OTzTCoOZ7RHmhD6CJoyhTDyhU3QVNNX3bvprL6K32wwAfq7GFgnYrI4lNxAMhZs9RNTNnAaLyh1h0cTVu9cF?purpose=fullsize'),
('The Chateau Spa & Wellness Resort','Bukit Tinggi, Pahang','A peaceful luxury resort surrounded by nature in Bukit Tinggi.',500.00,4.8,'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0f/1d/19/a1/the-chateau-spa-organic.jpg?w=700&h=-1&s=1'),
('Mangala Estate Boutique Resort','Gambang, Kuantan','A relaxing boutique resort surrounded by tropical greenery near Kuantan.',480.00,4.7,'https://blog.frankyhotels.com/wp-content/uploads/2025/05/490495101_1110252351141039_6060903502952696955_n.jpg'),
('Colmar Tropicale Resort','Bukit Tinggi, Bentong','A unique resort inspired by a French village located in the hills of Bukit Tinggi.',250.00,4.2,'https://th.bing.com/th/id/R.d8aee73852f2134a9c94762c7dbc8da6?rik=%2bYZakiKPz4n%2bWA&riu=http%3a%2f%2fwww.berjayahills.org%2fimg%2fcolmar-tropicale-resort.jpg&ehk=jKwBrzCG5mRtu2oAQoC6NJ%2bnnY6qx0y6iqvBZvl2%2fFk%3d&risl=&pid=ImgRaw&r=0'),
('Hilton Kota Kinabalu','Kota Kinabalu','A modern hotel located in the city centre of Kota Kinabalu.',450.00,4.5,'https://tse4.mm.bing.net/th/id/OIP.ApmJOIuIdXphQTwurxyImAHaE8?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
('The Magellan Sutera Resort','Kota Kinabalu','A luxury resort offering comfortable rooms and recreational facilities near the waterfront.',550.00,4.4,'https://www.fivestaralliance.com/files/fivestaralliance.com/field/image/nodes/2010/14584/14584_0_themagellansuteraresort_fsa-g.jpg'),
('Shangri-La Tanjung Aru Resort','Kota Kinabalu','A beachfront resort offering relaxing rooms and beautiful sunset views in Kota Kinabalu.',700.00,4.6,'https://www.travelweekly.com.au/wp-content/uploads/2015/02/Shangri-La-Tanjung-Aru.jpg'),
('Hilton Kuala Lumpur','KL Sentral','A modern luxury hotel conveniently located near KL Sentral and major attractions.',500.00,4.5,'https://th.bing.com/th/id/R.95378b2720b823f615d1bb30f3a7e454?rik=kd8LnA7YGNBXcQ&riu=http%3a%2f%2fphotos.wikimapia.org%2fp%2f00%2f08%2f11%2f30%2f04_full.jpg&ehk=%2b4ur4VXjTIveVcLnaVn8muB0EvVDK4aX8b1KutrzokM%3d&risl=&pid=ImgRaw&r=0'),
('The Ritz-Carlton','Kuala Lumpur,Bukit Bintang', 'A luxury hotel offering elegant rooms and convenient access to shopping and entertainment areas.',750.00,4.5,'https://list-sir.jp/express_assets/lp/the_ritz_carlton_residences_kualalumpur/img/img_about02@2x.jpg');



use hotel_booking;

insert into rooms(hotel_id,room_number,room_type,bed_type,price_per_night,status,image_url)values 
(1,'101','Deluxe Room','2 Single Bed',550.00,'available','https://pix8.agoda.net/hotelImages/5384/960252213/10d3c0bb84459cd1fc5ac8a974783885.jpeg?ce=2&s=1024x'),
(1,'102','Double Room','1 King Bed',620.00,'available','https://pix8.agoda.net/hotelImages/5384/3166516/65bd7efb519e57ed47459bbdf122e148.jpeg?ce=2&s=1024x'),
(1,'103','Suite Room','1 Queen Bed',850.00,'booked','https://pix8.agoda.net/hotelImages/5384/84452211/a2164816418a8c8838fc0f2f3b41e02c.jpeg?ce=2&s=1024x'), 

(2,'101','Deluxe Room','1 King Bed',500.00,'available','https://pix8.agoda.net/property/10468/1390725804/598e148d9de1ecb12b52631b7fdc3f64.jpeg?ce=3&s=1024x'),
(2,'102','Twin Room','2 Single Bed',550.00,'available','https://q-xx.bstatic.com/xdata/images/hotel/840x460/295683024.jpg?k=2171e1f0eff99f4bd65c54aafe9abdebaeea6206e35bcc02b604915d055dea90&o=&s=1024x'),
(2,'103','Suite Room','1 Queen Bed',800.00,'maintenance','https://pix8.agoda.net/property/88500614/1390725794/a56767443d17044f6acae6b7be0e0c1b.jpeg?ce=3&s=1024x'),

(3,'101','Double Room','1 Queen Bed',250.00,'available','https://ak-d.tripcdn.com/images/0581v12000spe1uy57EBE_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(3,'102','Twin Room','2 Single Bed',280.00,'available','https://ak-d.tripcdn.com/images/0585n12000so4a15v16E3_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(3,'103','Deluxe Room','1 Queen Bed',350.00,'booked','https://ak-d.tripcdn.com/images/22060j000000akmvl91A6_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(4,'101','Double Room','1 Queen Bed',300.00,'available','https://pix8.agoda.net/property/779306/0/cf93b879324c7bc0e01e9a005f41a1bc.jpeg?ce=2&s=1024x'),
(4,'102','Deluxe Room','1 King Bed',380.00,'available','https://pix8.agoda.net/hotelImages/779306/846594014/1e9ff98e4d65f480bbeebf64e3f642bc.jpeg?ce=2&s=1024x'),

(5,'101','Suite Room','1 Queen Bed',900.00,'available','https://pix8.agoda.net/hotelImages/1624261/18313139/4e80136f1e3d9eb547ad7cd5d8bb2da9.jpeg?ce=2&s=1024x'),
(5,'102','Deluxe Room','1 King Bed',400.00,'booked','https://q-xx.bstatic.com/xdata/images/hotel/840x460/772394404.jpg?k=bcc8f32a7b7f6e6f313c6d80c6f184ae399a35abc3229399f90bc9053c17aa8f&o=&s=1024x'),
(5,'103','Twin Room','2 Single Bed',330.00,'booked','https://ak-d.tripcdn.com/images/0584f12000czo3f3j8F58_R_600_400_R5.webp'),

(6,'101','Double Room','1 Queen Bed',380.00,'maintenance','https://ak-d.tripcdn.com/images/1mc4c12000gue9ubm94BB_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(6,'102','Twin Room','2 Single Bed',420.00,'available','https://ak-d.tripcdn.com/images/02029120009pvk8op2AFF_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(6,'103','Suite Room','1 Queen Bed',550.00,'booked','https://pix8.agoda.net/property/31182283/1382371306/14116de09e3935055155460ea0bc9e17.jpeg?ce=3&s=1024x'),

(7,'101','Double Room','1 Queen Bed',480.00,'available','https://pix8.agoda.net/hotelImages/443116/1015547768/78bd384286053caf8f8c028539a4d7b8.jpeg?ce=3&s=1024x'),
(7,'102','Twin Room','2 Single Bed',550.00,'maintenance','https://pix8.agoda.net/hotelImages/443116/3275489/9f0d8313cce9da27e7e9ba75204dab77.jpeg?ce=2&s=1024x'),
(7,'103','Suite Room','1 King Bed',850.00,'booked','https://ak-d.tripcdn.com/images/0586j12000soypp9a89B1_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(8,'101','Deluxe Room','1 King Bed',350.00,'available','https://ak-d.tripcdn.com/images/0202u120009435svo948C_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(8,'102','Double Room','1 King Bed',230.00,'available','https://ak-d.tripcdn.com/images/0204k120009vbsstr1D7E_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(8,'103','Family Room','1 King Bed',300.00,'available','https://ak-d.tripcdn.com/images/02027120009435zdt12A5_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(9,'101','Suite Room','1 King Bed',370.00,'booked','https://ak-d.tripcdn.com/images/200k1h000001hsvc5313A_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(9,'102','Double Room','1 Queen Bed',280.00,'booked','https://ak-d.tripcdn.com/images/0206a12000023e7g4EFE2_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(9,'103','Deluxe Room','1 King Bed',350.00,'available','https://ak-d.tripcdn.com/images/0201r12000023dkjj239E_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(10,'101','Double Room','1 King Bed',200.00,'available','https://ak-d.tripcdn.com/images/0583b12000soxnm768E90_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(10,'102','Twin Room','2 Single Bed',240.00,'available','https://ak-d.tripcdn.com/images/1mc6812000f4bzpkzCF45_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(10,'103','Deluxe Room','1 King Bed ',250.00,'maintenance','https://ak-d.tripcdn.com/images/1mc0t12000f4c06jeAC82_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(11,'101','Double Room','1 Queen Bed',300.00,'available','https://images.getaroom-cdn.com/image/upload/s--fhlkR5km--/c_limit,e_improve,fl_lossy.immutable_cache,h_940,q_auto:good,w_940/v1769362238/2f59c75a82759f25238c96550b675be35fa511d9?_a=BACAEuEv&atc=e7cd1cfa'),
(11,'102','Twin Room','2 Single Bed',350.00,'available','https://images.getaroom-cdn.com/image/upload/s--ZuE_5XDX--/c_limit,e_improve,fl_lossy.immutable_cache,h_940,q_auto:good,w_940/v1769362240/0979b08960bcdf02096138d79f45ed1fc6be3837?_a=BACAEuEv&atc=e7cd1cfa'),

(12,'101','Deluxe Room','1 Queen Bed',400.00,'available','https://img.cnt.traveloka.com/tvlk/apr-asset/TzEv3ZUmG4-4Dz22hvmO9NUDzw1DGCIdWl4oPtKumOg=/lodging/1000000/20000/16600/16576/b4066453_z.jpg?_src=imagekit&tr=dpr-2,c-at_max,h-460,q-40,w-724'),
(12,'102','Double Room','1 King Bed',490.00,'booked','https://img.cnt.traveloka.com/tvlk/apr-asset/oJLNzNs71wS3RVcWVniLgofXtaluprJ7ristt-jspoM=/images/0204112000929vc5h77B5_R_1080_808_R5_Mtrip.jpg?_src=imagekit&tr=dpr-2,c-at_max,h-460,q-40,w-724'),
(12,'103','Twin Room','2 Single Bed',550.00,'available','https://img.cnt.traveloka.com/tvlk/apr-asset/TzEv3ZUmG4-4Dz22hvmO9NUDzw1DGCIdWl4oPtKumOg=/lodging/1000000/20000/16600/16576/0596736b_z.jpg?_src=imagekit&tr=dpr-2,c-at_max,h-460,q-40,w-724'),

(13,'101','Deluxe Room','1 Queen Bed',450.00,'available','https://pix8.agoda.net/property/256255/1479233802/21a07f704d961d4e7102b92aab23ece3.jpeg?ce=3&s=1024x'),
(13,'102','Suite Room','1 King Bed',650.00,'maintenance','https://pix8.agoda.net/property/256255/1024312998/37179d2d0d3d7403f7f67bc4c2096eb6.jpeg?ce=2&s=1024x'),

(14,'101','Deluxe Room','1 Queen Bed',600.00,'booked','https://pix8.agoda.net/hotelImages/86357812/1416080255/2fbc2df20572d5fad558929570eb47d2.jpeg?ce=3&s=1024x'),
(14,'102','Suite Room','1 Queen Bed',480.00,'available','https://pix8.agoda.net/hotelImages/86357812/1450536802/7089a88405ac4fb3db1247688283f6b5.jpeg?ce=3&s=1024x'),

(15,'101','Double Room','1 Queen Bed',220.00,'available','https://ak-d.tripcdn.com/images/1mc5x12000b0e57op952B_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(15,'102','Family Room','1 King Bed',300.00,'booked','https://ak-d.tripcdn.com/images/1mc6w12000b0e20uv80DD_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(16,'101','Double Room','1 Queen Bed',400.00,'available','https://pix8.agoda.net/hotelImages/1273712/8998475/397ace7b6c37cb4afc1862c4bd26f774.jpeg?s=1024x'),
(16,'102','Deluxe Room','2 Single Bed',500.00,'available','https://pix8.agoda.net/hotelImages/1273712/8998517/1355497ac1830b7a935b7fb47b87d598.jpeg?ce=2&s=1024x'),

(17,'101','Deluxe Room','1 King Bed',480.00,'available','https://ak-d.tripcdn.com/images/1mc2x12000jnj5age4F20_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(17,'102','Twin Room','2 Single Bed',650.00,'booked','https://ak-d.tripcdn.com/images/1mc5l12000jnji6rbA8B0_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(18,'101','Deluxe Room','2 Single Bed',600.00,'maintenance','https://ak-d.tripcdn.com/images/1mc1w12000d4w33un8761_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(18,'102','Suite Room','1 King Bed',850.00,'available','https://ak-d.tripcdn.com/images/0200u120007reuxhhCF11_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),

(19,'101','Suite Room','1 Queen Bed',450.00,'available','https://pix8.agoda.net/hotelImages/6961/3136605/e55b0fc87ea4c48ee352509837b3d942.jpeg?ce=2&s=1024x'),
(19,'102','Family Room','1 King Bed',550.00,'booked','https://pix8.agoda.net/hotelImages/6961/3143663/5f41e8625806b0fa870811fa15198f03.jpeg?ce=2&s=1024x'),

(20,'101','Deluxe Room','1 Queen Bed',650.00,'available','https://ak-d.tripcdn.com/images/0201t1200081w2dtz4957_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F'),
(20,'102','Suite Room','1 King Bed',900.00,'available','https://ak-d.tripcdn.com/images/1mc6d12000esd5ni978C2_W_1280_853_R5.webp?proc=watermark/image_trip1,l_ne,x_16,y_16,w_67,h_16;digimark/t_image,logo_tripbinary;ignoredefaultwm,1A8F');

