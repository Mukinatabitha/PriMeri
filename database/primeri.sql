-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 10, 2025 at 05:39 PM
-- Server version: 12.0.2-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `primeri`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('Shipping','Billing','Factory','Headquarters') NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reorder_point` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturer_categories`
--

CREATE TABLE `manufacturer_categories` (
  `manufacturer_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `business_owner_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `deadline` date DEFAULT NULL,
  `status` enum('pending','paid','in_production','shipped','delivered','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `business_owner_id`, `manufacturer_id`, `order_date`, `deadline`, `status`, `total_amount`, `shipping_address`, `customer_name`, `customer_email`, `customer_phone`) VALUES
(3037, 1, 1, '2025-11-09 03:04:00', '2025-11-16', 'pending', 56250.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(3360, 1, 1, '2025-11-09 03:05:51', '2025-11-16', 'pending', 56250.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(7945, 1, 1, '2025-11-09 03:04:47', '2025-11-16', 'pending', 56250.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(18480, 1, 1, '2025-11-09 03:22:08', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(24542, 1, 1, '2025-11-09 03:38:45', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(29165, 1, 1, '2025-11-09 03:36:40', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(30331, 1, 1, '2025-11-09 03:21:46', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(32502, 1, 1, '2025-11-09 03:25:02', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(35336, 1, 1, '2025-11-09 03:39:48', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(41750, 1, 1, '2025-11-09 03:24:18', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(60959, 1, 1, '2025-11-09 03:24:10', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(62413, 1, 1, '2025-11-09 03:23:07', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(74128, 1, 1, '2025-11-09 03:33:34', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011'),
(83084, 1, 1, '2025-11-09 03:19:24', '2025-11-16', 'paid', 24000.00, 'garden estate, Nairobi, Kenya - 00618', 'Maxwell Kimani', 'maxkimani12@gmail.com', '0703689011');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `item_name`, `description`, `quantity`, `unit_price`) VALUES
(1, 3037, 2602, 'Custom Speaker Systems', 'No description available', 25, 1500.00),
(2, 3037, 2601, 'Branded Headphones', 'No description available', 25, 750.00),
(3, 7945, 2602, 'Custom Speaker Systems', 'No description available', 25, 1500.00),
(4, 7945, 2601, 'Branded Headphones', 'No description available', 25, 750.00),
(5, 3360, 2602, 'Custom Speaker Systems', 'No description available', 25, 1500.00),
(6, 3360, 2601, 'Branded Headphones', 'No description available', 25, 750.00),
(7, 83084, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(8, 30331, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(9, 18480, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(10, 62413, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(11, 60959, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(12, 41750, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(13, 32502, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(14, 74128, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(15, 29165, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(16, 24542, 302, 'Private Label Juices', 'No description available', 200, 120.00),
(17, 35336, 302, 'Private Label Juices', 'No description available', 200, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `payment_reference` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `amount`, `payment_date`, `payment_method`, `status`, `payment_reference`, `created_at`) VALUES
(1, '35336', 24000.00, '2025-11-09 03:39:48', 'M-Pesa', 'completed', 'MPESA176264878844430', '2025-11-09 00:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `minOrder` int(11) DEFAULT NULL,
  `inStock` tinyint(1) DEFAULT 1,
  `customization` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `name`, `description`, `price`, `category`, `image`, `minOrder`, `inStock`, `customization`) VALUES
(101, 1, 'Premium Cotton T-Shirts', 'High-quality 100% cotton t-shirts with custom printing. Perfect for corporate events and promotional giveaways.', 450.00, 'tshirts', '../images/tshirt-product.jpg', 50, 1, 'Logo Printing,Custom Colors,Sizes: S-XXL'),
(102, 1, 'Embroidered Hoodies', 'Premium fleece hoodies with professional embroidery. Available in multiple colors and sizes.', 1200.00, 'hoodies', '../images/hoodie-product.jpg', 50, 1, 'Logo Embroidery,Custom Text,Sizes: S-XXL'),
(103, 1, 'Corporate Polo Shirts', 'Professional polo shirts for corporate uniforms. Durable fabric with custom embroidery options.', 850.00, 'uniforms', '../images/polo-product.jpg', 50, 1, 'Logo Embroidery,Custom Colors,Sizes: S-XXL'),
(104, 1, 'Branded Jackets', 'Weather-resistant jackets with custom printing. Ideal for outdoor corporate events.', 1800.00, 'uniforms', '../images/jacket-product.jpg', 50, 1, 'Logo Printing,Custom Design,Sizes: S-XXL'),
(105, 1, 'Baseball Caps', 'Adjustable baseball caps with custom embroidery. Perfect for branding and promotions.', 350.00, 'accessories', '../images/cap-product.jpg', 50, 1, 'Logo Embroidery,Custom Colors'),
(106, 1, 'Tote Bags', 'Eco-friendly canvas tote bags with custom printing. Great for corporate gifts and events.', 280.00, 'accessories', '../images/tote-product.jpg', 50, 1, 'Logo Printing,Custom Design'),
(201, 2, 'Custom Branded Snack Boxes', 'Premium snack assortment with custom branding for corporate gifting and events.', 1200.00, 'snacks', '../images/snack-box.jpg', 100, 1, 'Custom Branding,Snack Selection,Packaging Design'),
(202, 2, 'Artisanal Chocolate Collection', 'Handcrafted chocolates with custom packaging for corporate gifts and promotions.', 850.00, 'confectionery', '../images/chocolate.jpg', 100, 1, 'Custom Wrapping,Logo Printing,Flavor Selection'),
(203, 2, 'Premium Coffee Blends', 'Specialty coffee blends with custom labeling for corporate clients and events.', 450.00, 'beverages', '../images/coffee-blend.jpg', 100, 1, 'Custom Roasts,Brand Labeling,Packaging Options'),
(301, 3, 'Custom Label Water', 'Premium bottled water with custom labeling for events and corporate branding.', 45.00, 'water', '../images/custom-water.jpg', 200, 1, 'Label Design,Bottle Size,Water Source'),
(302, 3, 'Private Label Juices', 'Fresh pressed juices with custom formulations and branding options.', 120.00, 'juices', '../images/custom-juice.jpg', 200, 1, 'Flavor Creation,Nutrition Profile,Packaging Design'),
(303, 3, 'Functional Energy Drinks', 'Custom formulated energy drinks with branded packaging.', 150.00, 'energy-drinks', '../images/energy-drink.jpg', 200, 1, 'Formula Development,Brand Packaging,Functional Ingredients'),
(401, 4, 'Custom Retail Boxes', 'Premium custom boxes for retail products with brand-specific designs.', 25.00, 'boxes', '../images/retail-box.jpg', 500, 1, 'Box Design,Material Selection,Printing Options'),
(402, 4, 'Branded Shopping Bags', 'Custom shopping bags with brand logos and designs for retail stores.', 12.00, 'bags', '../images/shopping-bag.jpg', 500, 1, 'Bag Style,Print Quality,Handle Options'),
(403, 4, 'Product Labels & Stickers', 'Custom labels and stickers for product branding and information.', 8.00, 'labels', '../images/product-label.jpg', 500, 1, 'Label Design,Material Type,Adhesive Options'),
(501, 5, 'Sustainable Gift Boxes', 'Curated gift boxes with eco-friendly products and sustainable packaging.', 1500.00, 'gift-boxes', '../images/eco-gift-box.jpg', 50, 1, 'Product Selection,Packaging Design,Brand Elements'),
(502, 5, 'Corporate Wellness Kits', 'Wellness-focused gift kits with sustainable products for employees.', 850.00, 'wellness-kits', '../images/wellness-kit.jpg', 50, 1, 'Kit Contents,Branding Options,Packaging Style'),
(503, 5, 'Eco-Friendly Promotional Items', 'Sustainable promotional products with custom branding.', 280.00, 'promotional', '../images/eco-promo.jpg', 50, 1, 'Product Selection,Brand Application,Material Options'),
(601, 6, 'Custom Engraved Keychains', 'Premium keychains with precision laser engraving for corporate branding.', 120.00, 'keychains', '../images/engraved-keychain.jpg', 50, 1, 'Material Selection,Design Complexity,Finish Options'),
(602, 6, 'Award Plaques & Trophies', 'Custom plaques and trophies with detailed engraving for corporate recognition.', 450.00, 'plaques', '../images/award-plaque.jpg', 50, 1, 'Plaque Design,Engraving Details,Material Quality'),
(603, 6, 'Corporate Branded Items', 'Various corporate items with precision engraving for promotions.', 280.00, 'corporate-items', '../images/branded-items.jpg', 50, 1, 'Item Selection,Engraving Placement,Design Requirements'),
(701, 7, 'Custom Formulated Soaps', 'Private label soaps with custom scents and formulations for brands.', 45.00, 'soaps', '../images/custom-soap.jpg', 100, 1, 'Scent Development,Formula Creation,Packaging Design'),
(702, 7, 'Branded Lotions & Creams', 'Custom formulated lotions and creams with private labeling.', 85.00, 'lotions', '../images/custom-lotion.jpg', 100, 1, 'Skin Type Formulation,Scent Options,Packaging Style'),
(703, 7, 'Hand Sanitizer Solutions', 'Custom branded hand sanitizers with various scent and formula options.', 35.00, 'sanitizers', '../images/custom-sanitizer.jpg', 100, 1, 'Formula Strength,Scent Selection,Bottle Design'),
(801, 8, 'Custom Power Banks', 'High-capacity power banks with precise logo placement for branding.', 450.00, 'power-banks', '../images/custom-powerbank.jpg', 100, 1, 'Capacity Options,Branding Placement,Color Selection'),
(802, 8, 'Branded Phone Cases', 'Premium phone cases with custom printing and brand logos.', 280.00, 'phone-cases', '../images/branded-case.jpg', 100, 1, 'Phone Models,Print Quality,Case Material'),
(803, 8, 'Custom Charging Cables', 'Durable charging cables with custom colors and branding.', 150.00, 'cables', '../images/custom-cable.jpg', 100, 1, 'Cable Type,Color Options,Branding Method'),
(1101, 11, 'Sublimation Printed T-Shirts', 'Vibrant all-over print t-shirts using advanced sublimation technology for lasting designs.', 550.00, 'tshirts', '../images/sublimation-tshirt.jpg', 50, 1, 'All-over Printing,Custom Designs,Eco-friendly Inks'),
(1102, 11, 'Eco-Friendly Activewear', 'Performance activewear made from recycled materials with moisture-wicking technology.', 950.00, 'activewear', '../images/activewear.jpg', 50, 1, 'Custom Colors,Brand Logos,Performance Fit'),
(1103, 11, 'Sustainable Hoodies', 'Comfortable hoodies made from organic cotton with eco-friendly printing options.', 1100.00, 'hoodies', '../images/eco-hoodie.jpg', 50, 1, 'Organic Materials,Eco-Printing,Custom Fit'),
(1201, 12, 'Executive Blazers', 'Premium tailored blazers for corporate executives with custom embroidery options.', 2500.00, 'business', '../images/blazer.jpg', 25, 1, 'Custom Tailoring,Logo Embroidery,Premium Fabrics'),
(1202, 12, 'Hospitality Uniforms', 'Professional uniforms for hotel and restaurant staff with durable, easy-care fabrics.', 750.00, 'uniforms', '../images/hospitality-uniform.jpg', 25, 1, 'Custom Colors,Brand Logos,Multiple Sizes'),
(1203, 12, 'Corporate Dress Shirts', 'Classic dress shirts with custom embroidery for professional corporate attire.', 650.00, 'business', '../images/dress-shirt.jpg', 25, 1, 'Custom Fit,Logo Placement,Fabric Options'),
(1301, 13, 'Organic Granola Bars', 'Nutritious organic granola bars with custom packaging for health-focused brands.', 380.00, 'snacks', '../images/granola-bar.jpg', 75, 1, 'Custom Flavors,Organic Certification,Brand Packaging'),
(1302, 13, 'Vegan Protein Snacks', 'Plant-based protein snacks with clean ingredients and custom branding options.', 520.00, 'snacks', '../images/vegan-snack.jpg', 75, 1, 'Vegan Formulations,Custom Sizes,Nutrition Labeling'),
(1303, 13, 'Gluten-Free Baked Goods', 'Delicious gluten-free baked products with custom packaging for dietary needs.', 680.00, 'baked-goods', '../images/gluten-free.jpg', 75, 1, 'Gluten-Free Options,Custom Recipes,Allergen Information'),
(1401, 14, 'International Spice Blends', 'Authentic spice blends from around the world with custom packaging.', 320.00, 'spices', '../images/spice-blend.jpg', 50, 1, 'Custom Blends,Packaging Design,Cultural Authenticity'),
(1402, 14, 'Specialty Olive Oils', 'Premium imported olive oils with custom labeling for gourmet markets.', 850.00, 'ingredients', '../images/olive-oil.jpg', 50, 1, 'Custom Bottling,Label Design,Origin Selection'),
(1403, 14, 'Ethnic Sauce Collection', 'Authentic sauces from various cuisines with custom packaging.', 450.00, 'sauces', '../images/ethnic-sauce.jpg', 50, 1, 'Recipe Customization,Packaging Options,Cultural Styles'),
(1501, 15, 'Signature Coffee Blends', 'Custom roasted coffee blends with branded packaging for corporate clients.', 680.00, 'coffee', '../images/coffee-blend.jpg', 100, 1, 'Custom Roasting,Blend Creation,Packaging Design'),
(1502, 15, 'Premium Tea Selection', 'Curated tea varieties with custom packaging for corporate gifting.', 450.00, 'tea', '../images/tea-selection.jpg', 100, 1, 'Tea Blending,Packaging Options,Brand Labeling'),
(1503, 15, 'Brewing Accessories Kit', 'Complete coffee brewing kit with custom branding for corporate clients.', 1200.00, 'accessories', '../images/brewing-kit.jpg', 100, 1, 'Kit Contents,Branding Elements,Instruction Materials'),
(1601, 16, 'Custom Craft Sodas', 'Artisanal craft sodas with unique flavor profiles and custom labeling.', 95.00, 'sodas', '../images/craft-soda.jpg', 150, 1, 'Flavor Development,Label Design,Bottle Selection'),
(1602, 16, 'Premium Cocktail Mixers', 'Specialty mixers for cocktails with custom formulations and packaging.', 180.00, 'mixers', '../images/cocktail-mixer.jpg', 150, 1, 'Mixer Formulas,Packaging Design,Usage Instructions'),
(1603, 16, 'Limited Edition Beverages', 'Seasonal and limited edition beverages with exclusive packaging.', 120.00, 'specialty', '../images/limited-edition.jpg', 150, 1, 'Seasonal Themes,Exclusive Packaging,Limited Production'),
(1701, 17, 'Compostable Packaging', 'Fully compostable packaging solutions for eco-conscious brands.', 35.00, 'compostable', '../images/compostable-packaging.jpg', 300, 1, 'Material Selection,Design Options,Certification Requirements'),
(1702, 17, 'Recycled Material Boxes', 'Packaging made from 100% recycled materials with custom printing.', 28.00, 'recycled', '../images/recycled-box.jpg', 300, 1, 'Recycled Content,Printing Methods,Box Styles'),
(1703, 17, 'Sustainable Mailers', 'Eco-friendly shipping mailers made from sustainable materials.', 15.00, 'mailers', '../images/eco-mailer.jpg', 300, 1, 'Material Options,Size Variations,Brand Printing'),
(1801, 18, 'Foil-Stamped Luxury Boxes', 'Premium boxes with foil stamping and embossing for luxury products.', 85.00, 'luxury-boxes', '../images/foil-box.jpg', 200, 1, 'Foil Colors,Embossing Patterns,Material Quality'),
(1802, 18, 'Custom Presentation Inserts', 'Luxury inserts and padding for high-value product presentation.', 45.00, 'inserts', '../images/presentation-insert.jpg', 200, 1, 'Insert Design,Material Selection,Brand Elements'),
(1803, 18, 'Premium Gift Packaging', 'Complete gift packaging solutions with luxury finishes and materials.', 120.00, 'gift-packaging', '../images/gift-packaging.jpg', 200, 1, 'Complete Design,Material Options,Finishing Techniques'),
(1901, 19, 'Employee Recognition Gifts', 'Custom gifts for employee recognition programs and milestones.', 750.00, 'employee-gifts', '../images/employee-gift.jpg', 25, 1, 'Gift Selection,Personalization,Packaging Options'),
(1902, 19, 'Client Appreciation Sets', 'Premium gift sets for client appreciation and relationship building.', 1200.00, 'client-gifts', '../images/client-gift.jpg', 25, 1, 'Product Curation,Brand Alignment,Presentation Quality'),
(1903, 19, 'Holiday Gift Collections', 'Seasonal gift collections for corporate holiday giving programs.', 950.00, 'holiday-gifts', '../images/holiday-gift.jpg', 25, 1, 'Seasonal Themes,Budget Options,Delivery Scheduling'),
(2001, 20, 'Executive Gift Sets', 'Luxury gift sets for C-level executives and high-value clients.', 2500.00, 'executive-gifts', '../images/executive-gift.jpg', 10, 1, 'Premium Products,Personal Engraving,Luxury Packaging'),
(2002, 20, 'Branded Luxury Items', 'High-end branded items with subtle corporate branding.', 1800.00, 'luxury-items', '../images/luxury-item.jpg', 10, 1, 'Item Selection,Discreet Branding,Quality Materials'),
(2003, 20, 'Custom Corporate Awards', 'Premium awards and recognition items with custom engraving.', 3200.00, 'awards', '../images/corporate-award.jpg', 10, 1, 'Award Design,Engraving Details,Presentation Cases'),
(2101, 21, 'Industrial Metal Tags', 'Durable metal tags with permanent engraving for industrial use.', 85.00, 'metal-tags', '../images/metal-tag.jpg', 25, 1, 'Metal Type,Tag Size,Engraving Depth'),
(2102, 21, 'Corporate Nameplates', 'Professional nameplates with precision engraving for offices and facilities.', 150.00, 'nameplates', '../images/nameplate.jpg', 25, 1, 'Plate Design,Mounting Options,Finish Selection'),
(2103, 21, 'Serial Number Plates', 'Custom serial number plates for equipment and product identification.', 65.00, 'serial-plates', '../images/serial-plate.jpg', 25, 1, 'Numbering Systems,Plate Material,Installation Requirements'),
(2201, 22, 'Crystal Achievement Awards', 'Premium crystal awards with 3D laser engraving for corporate recognition.', 850.00, 'crystal-awards', '../images/crystal-award.jpg', 10, 1, 'Crystal Quality,Engraving Design,Award Size'),
(2202, 22, 'Glass Service Awards', 'Elegant glass awards with detailed engraving for service recognition.', 650.00, 'glass-awards', '../images/glass-award.jpg', 10, 1, 'Glass Type,Engraving Style,Base Options'),
(2203, 22, 'Custom Trophy Designs', 'Bespoke trophy designs with combination materials and engraving.', 1200.00, 'trophies', '../images/custom-trophy.jpg', 10, 1, 'Design Creation,Material Combination,Engraving Details'),
(2301, 23, 'Organic Supplement Formulas', 'Custom organic supplement formulations with private labeling.', 120.00, 'supplements', '../images/organic-supplement.jpg', 75, 1, 'Formula Development,Organic Certification,Packaging Design'),
(2302, 23, 'Essential Oil Blends', 'Custom essential oil blends with therapeutic properties and branding.', 95.00, 'essential-oils', '../images/essential-oil.jpg', 75, 1, 'Blend Creation,Therapeutic Properties,Bottle Design'),
(2303, 23, 'Natural Skincare Products', 'Organic skincare products with custom formulations and packaging.', 150.00, 'skincare', '../images/natural-skincare.jpg', 75, 1, 'Skin Type Formulation,Ingredient Selection,Packaging Options'),
(2401, 24, 'Luxury Skincare Collection', 'Premium skincare products with advanced formulations and elegant packaging.', 450.00, 'skincare', '../images/luxury-skincare.jpg', 50, 1, 'Product Formulation,Packaging Design,Brand Positioning'),
(2402, 24, 'Custom Cosmetic Lines', 'Bespoke cosmetic products with custom colors and formulations.', 320.00, 'cosmetics', '../images/custom-cosmetics.jpg', 50, 1, 'Color Development,Formula Creation,Packaging Style'),
(2403, 24, 'Anti-Aging Solutions', 'Advanced anti-aging products with clinical-grade formulations.', 680.00, 'anti-aging', '../images/anti-aging.jpg', 50, 1, 'Formula Strength,Clinical Testing,Packaging Luxury'),
(2501, 25, 'Wireless Charging Stations', 'Custom wireless charging solutions for modern office environments.', 650.00, 'wireless-chargers', '../images/wireless-charger.jpg', 50, 1, 'Charging Capacity,Design Integration,Brand Elements'),
(2502, 25, 'Conference Room Technology', 'Custom conference room tech with branded elements and integration.', 1200.00, 'conference-gear', '../images/conference-tech.jpg', 50, 1, 'Tech Integration,Branding Options,Installation Support'),
(2503, 25, 'Smart Office Devices', 'Connected office devices with custom branding and functionality.', 850.00, 'smart-devices', '../images/smart-office.jpg', 50, 1, 'Device Selection,Brand Integration,Functionality Setup'),
(2601, 26, 'Branded Headphones', 'Premium headphones with custom branding for corporate use and events.', 750.00, 'headphones', '../images/branded-headphones.jpg', 25, 1, 'Headphone Quality,Branding Placement,Color Options'),
(2602, 26, 'Custom Speaker Systems', 'Professional speaker systems with branded elements for events.', 1500.00, 'speakers', '../images/custom-speakers.jpg', 25, 1, 'System Configuration,Brand Integration,Technical Specifications'),
(2603, 26, 'Event AV Packages', 'Complete audio-visual packages with custom branding for corporate events.', 2800.00, 'av-packages', '../images/av-package.jpg', 25, 1, 'Package Contents,Branding Elements,Technical Requirements');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `minOrder` int(11) DEFAULT NULL,
  `leadTime` varchar(100) DEFAULT NULL,
  `customization` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `reviews` int(11) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `btnClass` varchar(50) DEFAULT 'bg-primary-custom'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `name`, `category`, `description`, `image`, `minOrder`, `leadTime`, `customization`, `rating`, `reviews`, `tags`, `features`, `btnClass`) VALUES
(1, 'Urban Threads Co.', 'apparel', 'Premium custom apparel manufacturer specializing in corporate uniforms and branded clothing.', '../images/Urban Threads Co..jpg', 50, '7-14 business days', 'Logo printing & embroidery', 4.80, 142, 'T-shirts,Hoodies,Corporate Uniforms,Embroidery', 'Premium quality materials,Custom branding options,Bulk order discounts,Fast turnaround times', 'bg-primary-custom'),
(2, 'Gourmet Pantry', 'food', 'Artisanal food producer creating custom-branded snacks, confections, and specialty foods.', '../images/Gourmet Pantry.jpg', 100, '7-12 business days', 'Custom packaging & branding', 4.70, 156, 'Snacks,Confectionery,Coffee & Tea,Gift Baskets', 'Artisanal production methods,Custom packaging design,Quality ingredients,Bulk ordering available', 'bg-secondary-custom'),
(3, 'Beverage Crafters', 'beverage', 'Specialists in private-label beverages including water, juices, and functional drinks.', '../images/Beverage Crafters.jpg', 200, '14-21 business days', 'Custom formulations & labeling', 4.60, 178, 'Bottled Water,Juices,Energy Drinks,Custom Labels', 'Custom beverage formulations,FDA-compliant production,Private labeling services,Quality testing', 'bg-primary-custom'),
(4, 'Packaging Prodigy', 'packaging', 'Custom packaging solutions for retail, e-commerce, and corporate branding needs.', '../images/Packaging Prodigy.jpg', 500, '10-20 business days', 'Full packaging design services', 4.80, 189, 'Boxes,Bags,Labels,Display Packaging', 'Complete design services,Sustainable material options,Prototype development,Bulk production capabilities', 'bg-secondary-custom'),
(5, 'Eco-Gifts Collective', 'gifts', 'Sustainable corporate gift solutions with eco-friendly packaging and products.', '../images/Eco-Gifts Collective.jpg', 50, '10-15 business days', 'Eco-friendly gift curation', 4.80, 198, 'Gift Boxes,Promotional Items,Eco-Friendly,Custom Sets', 'Sustainable product sourcing,Eco-friendly packaging,Custom gift curation,Corporate branding options', 'bg-primary-custom'),
(6, 'Precision Engraving', 'engraving', 'Laser engraving specialists for wood, metal, and acrylic promotional products.', '../images/Precision Engraving.jpg', 50, '7-10 business days', 'Precision laser engraving', 4.70, 145, 'Keychains,Plaques,Trophies,Corporate Awards', 'High-precision laser technology,Multiple material options,Detailed design capabilities,Quality assurance', 'bg-secondary-custom'),
(7, 'Pure Elements', 'health', 'Private label health, beauty, and personal care products with custom formulations.', '../images/Pure Elements.png', 100, '14-21 business days', 'Custom formulations & packaging', 4.70, 234, 'Soaps,Lotions,Sanitizers,Skincare', 'Custom formulation development,Natural ingredient options,FDA-compliant production,Private labeling services', 'bg-primary-custom'),
(8, 'TechStyle Gear', 'tech', 'Branded tech accessories and electronics with precision logo placement.', '../images/TechStyle Gear.png', 100, '10-15 business days', 'Precision tech branding', 4.70, 267, 'Power Banks,Phone Cases,Cables,Chargers', 'Precision branding technology,Quality electronic components,Custom product development,Bulk order capabilities', 'bg-secondary-custom'),
(11, 'Fashion Forward Prints', 'apparel', 'Modern apparel manufacturer with innovative printing techniques and sustainable fabrics.', '../images/Fashion Forward Prints.jpg', 50, '10-15 business days', 'Sublimation printing & eco-fabrics', 4.60, 89, 'Eco-Fabrics,Sublimation Printing,Activewear,Custom Designs', 'Sustainable fabric options,Advanced printing technology,Custom design services,Eco-friendly production', 'bg-secondary-custom'),
(12, 'Professional Wear Hub', 'apparel', 'Specialists in corporate and hospitality uniforms with premium finishing and detailing.', '../images/Professional Wear Hub.jpg', 25, '5-10 business days', 'Custom logos & professional tailoring', 4.90, 203, 'Business Attire,Hotel Uniforms,Restaurant Wear,Custom Logos', 'Professional tailoring services,Premium fabric selection,Industry-specific designs,Quick turnaround times', 'bg-primary-custom'),
(13, 'Organic Delights Co.', 'food', 'Certified organic food products with custom branding for health-conscious businesses.', '../images/Organic Delights Co..jpg', 75, '8-14 business days', 'Organic certification & custom labels', 4.80, 134, 'Organic Snacks,Gluten-Free,Vegan Options,Health Foods', 'Certified organic ingredients,Health-conscious formulations,Sustainable packaging,Custom nutrition labeling', 'bg-primary-custom'),
(14, 'Global Flavors Imports', 'food', 'International specialty foods and ingredients with custom packaging for diverse markets.', '../images/Global Flavors Imports.jpg', 50, '10-20 business days', 'International packaging & labeling', 4.50, 98, 'International Foods,Spices,Specialty Ingredients,Ethnic Cuisine', 'Global sourcing network,Authentic international flavors,Custom import packaging,Cultural authenticity', 'bg-secondary-custom'),
(15, 'Artisan Coffee Roasters', 'beverage', 'Premium coffee beans and custom blends with branded packaging for corporate clients.', '../images/Artisan Coffee Roasters.jpg', 100, '7-10 business days', 'Custom roasting & packaging', 4.90, 245, 'Coffee Beans,Custom Blends,Tea Selection,Brewing Accessories', 'Small-batch roasting,Custom blend development,Sustainable sourcing,Expert barista consultation', 'bg-secondary-custom'),
(16, 'Craft Soda Works', 'beverage', 'Custom formulated craft sodas and specialty beverages with unique flavor profiles.', '../images/Craft Soda Works.jpg', 150, '10-15 business days', 'Unique flavors & packaging', 4.70, 167, 'Craft Sodas,Mixers,Limited Editions,Custom Flavors', 'Artisan flavor creation,Natural ingredients,Custom bottle design,Limited edition options', 'bg-primary-custom'),
(17, 'Eco-Pack Solutions', 'packaging', 'Sustainable and biodegradable packaging options for environmentally conscious brands.', '../images/Eco-Pack Solutions.jpg', 300, '12-18 business days', 'Eco-friendly materials & design', 4.90, 234, 'Biodegradable,Recycled Materials,Compostable,Eco-Friendly', 'Certified sustainable materials,Carbon-neutral production,Compostable options,Environmental certifications', 'bg-primary-custom'),
(18, 'Luxury Packaging Co.', 'packaging', 'Premium packaging solutions for high-end products with custom finishes and materials.', '../images/Luxury Packaging Co..jpg', 200, '15-25 business days', 'Luxury finishes & materials', 4.70, 156, 'Premium Materials,Foil Stamping,Embossing,Custom Inserts', 'Luxury material selection,Premium finishing techniques,Custom insert design,High-end presentation', 'bg-secondary-custom'),
(19, 'Corporate Gifts Unlimited', 'gifts', 'Comprehensive corporate gifting solutions for employee recognition and client appreciation.', '../images/Corporate Gifts Unlimited.jpg', 25, '7-12 business days', 'Full gift program management', 4.60, 167, 'Employee Gifts,Client Appreciation,Holiday Packages,Bulk Orders', 'Complete gift program management,Bulk order discounts,Seasonal collections,Personalization services', 'bg-secondary-custom'),
(20, 'Luxury Gift Creations', 'gifts', 'High-end corporate gifts and executive presents with premium materials and customization.', '../images/Luxury Gift Creations.jpg', 10, '14-21 business days', 'Premium personalization', 4.90, 89, 'Executive Gifts,Luxury Items,Premium Brands,Personalization', 'Premium brand partnerships,Executive-level personalization,Luxury packaging,White-glove service', 'bg-primary-custom'),
(21, 'Metal Works Engraving', 'engraving', 'Specialists in metal engraving for industrial, corporate, and promotional applications.', '../images/Metal Works Engraving.jpg', 25, '8-12 business days', 'Industrial-grade metal engraving', 4.80, 112, 'Metal Tags,Industrial Marking,Nameplates,Serial Numbers', 'Industrial-grade equipment,Durable metal options,Permanent marking,Technical specifications', 'bg-primary-custom'),
(22, 'Crystal Awards Co.', 'engraving', 'Custom crystal and glass awards with precision laser engraving for corporate recognition.', '../images/Crystal Awards Co..jpg', 10, '10-15 business days', 'Crystal & glass laser engraving', 4.90, 178, 'Crystal Awards,Glass Engraving,Trophy Design,Recognition Items', 'Premium crystal materials,3D laser engraving,Custom award design,Presentation packaging', 'bg-secondary-custom'),
(23, 'Natural Wellness Co.', 'health', 'Organic and natural health products with custom formulations and private labeling.', '../images/Natural Wellness Co..png', 75, '12-18 business days', 'Organic formulations & labeling', 4.80, 189, 'Organic Products,Supplements,Essential Oils,Natural Remedies', 'Certified organic ingredients,Natural formulation expertise,Sustainable sourcing,Third-party testing', 'bg-secondary-custom'),
(24, 'Luxury Beauty Labs', 'health', 'Premium skincare and cosmetics with advanced formulations and elegant packaging.', '../images/Luxury Beauty Labs.png', 50, '15-25 business days', 'Luxury formulations & packaging', 4.90, 156, 'Skincare,Cosmetics,Anti-Aging,Luxury Packaging', 'Advanced formulation technology,Luxury packaging design,Clinical testing,Premium ingredient sourcing', 'bg-primary-custom'),
(25, 'Smart Office Tech', 'tech', 'Custom tech solutions for modern workplaces including branded devices and accessories.', '../images/Smart Office Tech.png', 50, '12-18 business days', 'Office tech customization', 4.60, 134, 'Office Tech,Wireless Chargers,Conference Gear,Smart Devices', 'Office-specific technology,Wireless charging solutions,Conference room integration,Smart office systems', 'bg-primary-custom'),
(26, 'Audio Visual Pros', 'tech', 'Custom audio and visual equipment with branding for corporate and event use.', '../images/Audio Visual Pros.png', 25, '15-25 business days', 'AV equipment branding', 4.80, 98, 'Headphones,Speakers,AV Equipment,Event Tech', 'Professional AV equipment,Event technology solutions,Custom branding integration,Technical support', 'bg-secondary-custom');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `accountType` enum('manufacturer','buyer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `accountType`) VALUES
(6, 'Maxwell Kimani', 'maxwellboy69@gmail.com', '$2y$10$C1//j5FcZP3OQu2FtrwXJeKpKJGjPnOvwf.iYThPtsbWB8hJ.uC3e', 'manufacturer'),
(9, 'SajjadCorp', 'mohammed.sajjad@strathmore.edu', '$2y$10$NY7SXqP/Ekt.xcHvdGfkWu5Owd0bQKXVO8kG5aWDsnBhBaXWzcf0.', 'buyer'),
(10, 'Natalie Abwoga', 'abwoganatalie@gmail.com', '$2y$10$GCRcr/nCfxpkiGKKPct.CecN6xluXkIc5n7v7zV8Mc3d8Ne5tM1fS', 'buyer'),
(11, 'Natalie', 'natalie.abwoga@strathmore.edu', '$2y$10$SIm8ArDqHUbvhL3d9yVscuvbfEPzr69LrE4FkgUNoRzjeOpw7Xsku', 'manufacturer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`);

--
-- Indexes for table `manufacturer_categories`
--
ALTER TABLE `manufacturer_categories`
  ADD PRIMARY KEY (`manufacturer_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83085;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manufacturer_categories`
--
ALTER TABLE `manufacturer_categories`
  ADD CONSTRAINT `manufacturer_categories_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `manufacturer_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
