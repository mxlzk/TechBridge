-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2026 at 11:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techbridge`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `device_id`, `quantity`, `created_at`) VALUES
(11, 5, 8, 2, '2026-06-23 08:26:02'),
(12, 5, 9, 2, '2026-06-23 08:26:05'),
(13, 6, 3, 4, '2026-06-23 14:13:45'),
(19, 6, 2, 2, '2026-06-23 14:53:43'),
(20, 6, 5, 1, '2026-06-23 14:55:18'),
(22, 5, 3, 3, '2026-06-24 02:55:26'),
(23, 5, 4, 3, '2026-06-24 02:55:49'),
(24, 5, 2, 3, '2026-06-24 02:55:57'),
(25, 5, 16, 1, '2026-06-24 03:01:01'),
(26, 5, 12, 1, '2026-06-24 03:03:18'),
(27, 5, 14, 1, '2026-06-24 03:09:47'),
(28, 5, 13, 1, '2026-06-24 03:44:23'),
(29, 5, 5, 2, '2026-06-24 04:05:03'),
(54, 2, 20, 4, '2026-07-19 13:18:45'),
(57, 2, 21, 5, '2026-07-28 15:26:01'),
(59, 2, 23, 6, '2026-08-02 18:33:16'),
(62, 2, 22, 2, '2026-08-02 18:40:10'),
(65, 18, 21, 1, '2026-08-02 20:38:44'),
(66, 18, 22, 9, '2026-08-02 20:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL,
  `device_name` varchar(255) NOT NULL,
  `device_os` varchar(100) NOT NULL,
  `device_type` varchar(100) NOT NULL,
  `device_color` varchar(100) NOT NULL,
  `device_storage` varchar(100) NOT NULL,
  `device_specs` text NOT NULL,
  `device_price` decimal(10,2) NOT NULL,
  `device_status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `device_quantity` int(11) NOT NULL DEFAULT 0,
  `device_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`device_id`, `device_name`, `device_os`, `device_type`, `device_color`, `device_storage`, `device_specs`, `device_price`, `device_status`, `device_quantity`, `device_image`, `created_at`, `updated_at`) VALUES
(2, 'iPhone X', 'iOS', 'Smartphone', 'White', '256GB', '3GB RAM, os support up to iOS 16.7.12', 899.00, 'Available', 222, 'deviceimages/product_6a0892f7ba46c4.10697390.jpg', '2026-05-16 07:53:27', '2026-07-28 09:52:25'),
(3, 'Oppo A6 5G', 'Android', 'Smartphone', 'Pink', '512GB', '8GB RAM\r\n7000mAh battery capacity\r\nIP69 Water and Dust Resistance\r\n60 months of warranty', 1599.00, 'Available', 220, 'deviceimages/product_6a0895e9633e84.53636528.png', '2026-05-16 08:06:01', '2026-07-28 09:52:17'),
(4, 'Vivo V60 Lite', 'Android', 'Smartphone', 'Black', '256GB', 'RAM: 8GB \r\nProcessor: MediaTek Dimensity 7360-Turbo \r\nBattery Capacity: 6500 mAh \r\nDimension 163.77 x 76.28 x 7.59 mm \r\nCharging Power: 90W', 1329.00, 'Available', 20, 'deviceimages/product_6a0f62a18524b1.31846049.jpg', '2026-05-16 08:15:52', '2026-07-28 15:32:47'),
(5, 'iPhone 12', 'iOS', 'Smartphone', 'Pacific Blue', '128GB', 'Display: 6.1-inch Super Retina XDR OLED display\r\nProcessor: Apple A14 Bionic chip (6-core CPU, 4-core GPU, 16-core Neural Engine)\r\nDurability: IP68 water and dust resistant (up to 6 meters for 30 minutes)\r\nBattery: Up to 17 hours of video playback; supports 15W MagSafe and 7.5W Qi wireless charging\r\nDimensions: 146.7 x 71.5 x 7.4 mm (5.78 x 2.81 x 0.29 inches)', 950.00, 'Available', 2, 'deviceimages/product_6a0b3e42160e97.05913169.jpg', '2026-05-18 08:28:50', '2026-07-28 10:25:51'),
(6, 'HP 15s-Eq2196AU 15.6\'\' FHD', 'Windows', 'Laptop', 'Gold', '512GB', 'AMD Ryzen™ 3 5300U processor\r\n8GB DDR4 3200 RAM (1x8GB, Upgradable)\r\n512GB PCIe NVMe SSD\r\nAMD Radeon™ Graphics\r\n15.6 \" FHD (1920 x 1080), micro-edge, anti-glare, 250 nits, 45% NTSC\r\nWindows 11 Home\r\n2 Years HP Onsite Warranty\r\nPre-installed MS Office Home & Student 2021', 1500.70, 'Available', 5, 'deviceimages/product_6a0f7f556ec1b5.47167519.png', '2026-05-21 13:55:33', '2026-07-28 15:30:55'),
(7, 'Infinix Hot 60 5G', 'Android', 'Smartphone', 'Caramel Glow', '256GB', 'Chipset: MediaTek Dimensity 7060 5G Chipset\r\nBattery capacity: 5200mAh \r\nRefresh rate: 120Hz \r\nDimension: 166mm x 76.8mm x 7.8mm\r\nWeight: 193g\r\nRAM: 8GB RAM + 8GB Extended RAM\r\nSupported Network: 5G/4G', 699.00, 'Available', 1, 'deviceimages/product_6a0fc94b51daf6.14230002.png', '2026-05-21 19:11:07', '2026-07-28 10:25:35'),
(8, 'ASUS Vivobook Go 14 (E1404F)', 'Windows', 'Laptop', 'Mixed Black/Green Gray', '512GB', 'Processor: AMD Ryzen™ 5 7520U Processor 2.8GHz (6MB Cache, up to 4.3GHz, 4 cores, 8 Threads)/AMD Ryzen™ 3 7320U Processor 2.4GHz (6MB Cache, up to 4.1GHz, 4 cores, 8 Threads)\r\nDisplay: 14.0-inch, FHD (1920 x 1080) 16:9 aspect ratio, IPS-level Panel, LED Backlit, Refresh Rate:60Hz, 250nits, 45% NTSC color gamut, Anti-glare display, Non-touch screen, 83% (Screen-to-body ratio)\r\nBattery: 42WHrs, 3S1P, 3-cell Li-ion\r\nPower Supply: 45W AC Adapter, Output: 19V DC, 2.37A, 45W, Input: 100~240V AC 50/60Hz universal\r\nWeight: 1.38 kg (3.04 lbs)\r\nDimensions (W x D x H): 32.55 x 21.39 x 1.79 ~ 1.79 cm (12.81\" x 8.42\" x 0.70\")\r\nInstalled Microsoft tools: Microsoft Office Home 2024 + Microsoft 365 Basic (with 100GB of cloud storage), Microsoft Office Home & Student 2021 + Microsoft 365 Basic', 1299.50, 'Available', 100, 'deviceimages/product_6a0fce119fc6d9.33503595.png', '2026-05-21 19:31:29', '2026-07-28 10:25:58'),
(9, 'Honor Pad V9', 'Android', 'Tablet', 'Grey', '256GB', 'Dimensions: 259.1 x 176.1 x 6.1 mm (10.20 x 6.93 x 0.24 in)\r\nWeight: 475 g (1.05 lb)\r\nChipset: Mediatek Dimensity 8350 (4 nm)\r\nBattery capacity: 10100 mAh\r\nRAM: 8GB RAM', 1889.00, 'Available', 100, 'deviceimages/product_6a1026c5c99e45.77684318.png', '2026-05-22 01:49:57', '2026-07-28 10:26:14'),
(12, 'Poco X7 5G', 'Android', 'Smartphone', 'Silver', '256GB', 'Processor: Dimensity 7300-Ultra\r\nRAM: 8GB\r\nDimensions: 162.33mm x 74.42mm x 8.4mm\r\nWeight: 185.5g\r\nDisplay: 6.67\" CrystalRes AMOLED display\r\nResolution: 2712 x 1220 (1.5K resolution)\r\nRefresh rate: Up to 120Hz\r\nBattery capacity: 5110mAh\r\nNetwork bandwidth: 5G', 939.00, 'Available', 100, 'deviceimages/product_6a146e2c5175c3.16629914.jpg', '2026-05-25 07:43:40', '2026-07-28 10:26:07'),
(13, 'Samsung A07 5G', 'Android', 'Smartphone', 'Light violet', '256GB', 'Display: 6.7-inch edge-to-edge PLS LCD with up to 90Hz (4G) or 120Hz (5G) refresh rates for smooth scrolling.\r\nCamera: Dual rear setup highlighted by a 50MP main camera, plus an 8MP front camera.\r\nProcessor: MediaTek Dimensity 6300\r\nRAM: 8GB\r\nNetwork bandwidth: 5G', 799.00, 'Available', 2, 'deviceimages/product_6a14713af04732.33800960.jpg', '2026-05-25 07:56:42', '2026-07-28 10:25:04'),
(14, 'Samsung Galaxy A26 5G', 'Android', 'Smartphone', 'Black', '256GB', 'Processor: MediaTek Dimensity 6300\r\nDimensions: 164 x 77.5 x 7.7 mm (6.46 x 3.05 x 0.30 in)\r\nDisplay: Super AMOLED, 120Hz\r\nResolution: 1080 x 2340 pixels, 19.5:9 ratio (~385 ppi density)\r\nProtection: Corning Gorilla Glass Victus+, Mohs level 5\r\nBattery: 5000 mAh', 1188.00, 'Available', 1, 'deviceimages/product_6a1474038c9ce7.80501035.png', '2026-05-25 08:08:35', '2026-07-28 10:25:40'),
(16, 'Realme C75', 'Android', 'Smartphone', 'Gold', '256GB', 'Processor: MediaTek Helio G92 Max Chipset\r\nBattery capacity: 6000mAh\r\nIP69 Dust & Water Resistance\r\nRAM: 8 GB \r\nScreen size: 6.72 inch (17.07cm)\r\nBrightness: 580nits (typ) / 690nits  (HBM)\r\nRefresh rate: Up to 90Hz\r\nTouch sampling rate: Up to 180 Hz\r\nResolution: 2400 x 1080 FHD+\r\nNetwork bandwidth: 4G\r\nSize: 165.69 x 76.22 x 7.99,\r\nWeight: 196g', 659.00, 'Available', 9, 'deviceimages/product_6a1476b74b4d09.33585806.png', '2026-05-25 08:20:07', '2026-07-28 10:18:53'),
(17, 'Oppo A5 5G', 'Android', 'Smartphone', 'Green', '256GB', '1 Year Warranty\r\n6.67-inch Display\r\nColorOS 15.0\r\nDimensions: 165.7 x 76.2 x 8 mm\r\nWeight: 193g\r\nBattery Capacity: 6000mAh\r\nScreen Size: 6.67 inch IPS LCD\r\nResolution: 720 x 1604 pixels\r\nProcessor: Qualcomm Snapdragon 6s Gen1 4G\r\nRAM: 8GB\r\nInternal Storage: 128GB or 256GB\r\nRear Camera: 50-megapixel + 2-megapixel\r\nFront Camera: 5-megapixel\r\nOperating System: Android 15\r\nSupport: Dual SIM & 4G/LTE', 599.00, 'Available', 11, 'deviceimages/device_6a3d73f23fabe5.27758671.jpg', '2026-06-25 18:31:14', '2026-07-28 10:25:12'),
(18, 'Oppo A77 5G', 'Android', 'Smartphone', 'Ocean Blue', '128GB', 'CPU: Octa-core (2x2.4 GHz Cortex-A76 & 6x2.0 GHz Cortex-A55)\r\nChipset: MediaTek MT6833P Dimensity 810 (6 nm)\r\nGPU: Mali-G57 MC2\r\nMemory: 6GB (+5GB) +128GB\r\nCard Slot: microSDXC\r\nBattery: 5000 mAh, non-removable battery\r\nFast charging: 33W, 53% in 30 min (advertised)\r\nOS: Android 12, ColorOS 12.1\r\nDisplay Type: IPS LCD, 90Hz, 480 nits (typ), 600 nits (HBM)\r\nDisplay Size: 6.56 inches, 103.4 cm2 (~84.0% screen-to-body ratio) 720 x 1612 pixels, 20:9 ratio (~269 ppi density)\r\nBody Dimension: 163.8 x 75.1 x 8 mm\r\nBody Weight: 190g\r\nBody Type: Glass front, plastic frame, plastic back\r\nIPX4 water-resistant\r\nRear Camera Dual: 48 MP, f/1.7, 26mm (wide), PDAF + 2 MP, f/2.4, (depth)\r\nFront Camera: 8 MP, f/2.0, 26mm (wide)\r\nVideo: 1080p@30fps\r\nSelfie: 1080p@30fps\r\nCamera Features: LED flash, HDR, panorama\r\nSelfie: Panorama\r\nSim Card: Dual SIM (Nano-SIM, dual stand-by)\r\nData Speed: HSPA 42.2/5.76 Mbps, LTE, 5G\r\nWLAN: Wi-Fi 802.11 a/b/g/n/ac, dual-band, Wi-Fi Direct, hotspot\r\nBluetooth: 5.3, A2DP, LE, aptX HD\r\nNFC	Support\r\nUSB	USB Type-C 2.0, USB On-The-Go', 650.00, 'Unavailable', 0, 'deviceimages/device_6a3d76956a6e35.01128473.jpg', '2026-06-25 18:42:29', '2026-07-20 00:50:07'),
(20, 'Lenovo THINKPAD T14s GEN 3', 'Windows', 'Laptop', 'Black', '512GB', 'CORE i7-1265U Vpro (10 CORE / 12 THREADS)\r\n32GB RAM DDR5 4800MHZ\r\n512GB SSD NVME\r\nINTEL IRIS XE GRAPHCIS\r\n14 INCH FHD+ IPS DISPLAY\r\nWINDOWS HELLO / BIOMETRICS\r\nWINDOWS 11 PRO\r\nMS OFFICE 2024 PRO PLUS', 1224.50, 'Available', 10, 'deviceimages/device_6a55c99f110678.82286045.jpg', '2026-07-14 05:31:11', '2026-07-28 10:24:53'),
(21, 'Xiaomi Redmi Pad 2', 'Android', 'Tablet', 'Graphite Gray', '256GB', 'Height: 254.58 mm\r\nWidth: 166.04 mm\r\nThickness: 7.36 mm\r\nInternet latency: 5G\r\nWeight: 510g\r\nProcessor: MediaTek Helio G100-Ultra\r\nStorage & RAM\r\nRAM: 8GB\r\nRefresh rate: Up to 90Hz\r\nBattery capacity: 9000mAh\r\nCharging speed: 18W', 749.25, 'Available', 20, 'deviceimages/device_6a5914ad8d6df5.41292855.jpg', '2026-07-16 15:09:18', '2026-08-02 20:27:09'),
(22, 'iPhone 11 Pro', 'iOS', 'Smartphone', 'Gold', '256GB', 'Dimensions: 144 x 71.4 x 8.1 mm\r\nWeight: 188 g\r\nIP rating (protection against dust and water): IP68 Dust and water resistant (up to 4 m for 30 minutes)\r\nScreen type: XDR OLED touch screen, 16 million colours\r\nScreen size: 5.8 inches\r\nResolution: 1125 x 2436 pixels\r\nMain camera: Triple 12 MP, f/1.8, 26mm (wide) + 12 MP, f/2.0, 52mm (telephoto) + 12 MP, f/2.4, 13mm (ultra wide)\r\nFront camera: Dual 12 MP, f/2.2 + TOF 3D camera\r\nVideo recorder: 2160p@24/30/60fps, 1080p@30/60/120/240fps, HDR\r\nFlash: Yes\r\nMusic player: Yes\r\nSpeaker:	Stereo\r\nSIM type: Nano SIM; eSIM\r\nChipset: Apple A13 Bionic (7 nm+)\r\nCPU: Hexa-core (2x2.65 GHz Lightning + 4x1.8 GHz Thunder)\r\nGPU: Apple GPU (4-core graphics)', 989.00, 'Available', 25, 'deviceimages/device_6a5f3b43a937c3.82288649.jpg', '2026-07-21 09:26:27', '2026-07-28 10:27:28'),
(23, 'Oppo Reno11 5G', 'Android', 'Smartphone', 'Rock Gray', '256GB', 'Body\r\nDimensions: 162.4 x 74.3 x 7.9 mm or 8.0 mm\r\nWeight: 182 g (6.42 oz)\r\nSIM: Dual SIM (Nano-SIM, dual stand-by)\r\n\r\nPlatform\r\nOS: Android 14, ColorOS 14\r\nChipset: Mediatek Dimensity 7050 (6 nm)\r\nCPU: Octa-core (2x2.6 GHz Cortex-A78 & 6x2.0 GHz Cortex-A55)\r\nGPU: Mali-G68 MC4\r\n\r\nCamera\r\nRear Camera: 50 MP, f/1.8, 26mm (wide), 1/1.95\", PDAF, OIS\r\n32 MP, f/2.0, 47mm (telephoto), 1/2.74\", 0.8µm, PDAF, 2x optical zoom\r\n8 MP, f/2.2, 16mm, 112˚ (ultrawide), 1/4.0\", 1.12µm\r\nSelfie Camera: 32 MP, f/2.4, 22mm (wide), 1/2.74\", 0.8µm', 899.00, 'Available', 25, 'deviceimages/device_6a5f3e53bc2732.93444918.png', '2026-07-21 09:39:31', '2026-07-28 10:27:39'),
(24, 'Honor 600 Lite', 'Android', 'Smartphone', 'Desert Gold', '256GB', 'Display Type: AMOLED\r\nRefresh Rate: 120Hz\r\nPlayback Quality: SDR\r\nLTPO Support: No\r\nLTPS Support: No\r\nAnti-Reflection Coating: No\r\nDisplay Size: 6.6 inches\r\nArea: 106.9cm²\r\nScreen-to-Body Ratio: 90.1\r\nDisplay Form Factor: Flat Display\r\n\r\nDisplay Resolution: 1.5K\r\nResolution Width: 1200pixels\r\nResolution Height: 2600 pixels\r\nAspect Ratio: 19.5:9\r\nPixel Density: 434 ppi\r\nDisplay Bit Rate: 10-bit\r\nColour Depth: 1.07 Billion Colours\r\nTypical Brightness: nits\r\nHigh Brightness: 2000 nits\r\nPeak Brightness: 6500 nits\r\n\r\nDimensions: 157.43 x 75.35 x 7.34 mm\r\nWeight: 180 grams\r\nFront: Aluminosilicate Glass\r\nBuild Quality: SGS 5-Star Drop Resistance\r\nPorts: USB Type-C 2.0, OTG, USB Type-C (bottom)\r\nIP Rating: IP66 (Dust and Water Resistant)\r\nSpeaker: Stereo Speakers, HONOR Sound\r\nChipset: MediaTek Dimensity 7100 Elite\r\nNumber of Cores: Octa-core\r\nClock Speed: 4*A78 2.4 GHz + 4*A55 2.0 GHz\r\nGPU: Mali-G610 MC2\r\nSoftware Updates: 6 years of Software and Security updates\r\nMain Camera: 108 MP\r\nVideo Recording: 1080p@30fps\r\nLog Video Recording Support: No\r\nVideo Features: 120fps Slo-mo, Dual Video, Time-lapse Video\r\nBattery Capacity: 6520 mAh\r\nCharging Speed: 45W\r\nRAM: 12 GB\r\nInternet Connectivity: 5G', 999.00, 'Available', 300, 'deviceimages/device_6a62e9b9103720.48678362.jpg', '2026-07-24 04:27:37', '2026-07-28 15:40:42');

-- --------------------------------------------------------

--
-- Table structure for table `device_orders`
--

CREATE TABLE `device_orders` (
  `order_item_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_status` enum('Pending','Approved','Rejected','Preparing','Shipped','Delivered') DEFAULT 'Pending',
  `total_price` decimal(10,2) NOT NULL,
  `order_remarks` text DEFAULT NULL,
  `supplier_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_orders`
--

INSERT INTO `device_orders` (`order_item_id`, `order_id`, `supplier_id`, `device_id`, `quantity`, `order_status`, `total_price`, `order_remarks`, `supplier_remarks`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260625125845-189', 3, 2, 1, 'Pending', 0.00, 'testing', NULL, '2026-06-25 10:58:45', '2026-06-25 10:58:45'),
(2, 'ORD-20260627031235-641', 9, 12, 1, 'Rejected', 0.00, 'testing 123', '', '2026-06-27 01:12:35', '2026-06-29 11:28:35'),
(3, 'ORD-20260627032059-606', 7, 14, 3, 'Pending', 0.00, 'not sufficient units', NULL, '2026-06-27 01:20:59', '2026-06-27 01:20:59'),
(4, 'ORD-20260627032159-487', 9, 18, 1, 'Shipped', 0.00, '', 'can proceed to inform user items are stock ready', '2026-06-27 01:21:59', '2026-06-29 11:28:06'),
(5, 'ORD-20260627032159-487', 9, 5, 2, 'Shipped', 0.00, '', 'can proceed to inform user items are stock ready', '2026-06-27 01:21:59', '2026-06-29 11:28:06'),
(6, 'ORD-20260714112130-416', 3, 6, 3, 'Pending', 6030.00, 'Testing to pass the pricee through', NULL, '2026-07-14 09:21:30', '2026-07-14 09:21:30'),
(7, 'ORD-20260714112130-416', 3, 7, 2, 'Pending', 1398.00, 'Testing to pass the pricee through', NULL, '2026-07-14 09:21:30', '2026-07-14 09:21:30'),
(8, 'ORD-20260716193222-669', 11, 21, 2, 'Pending', 1498.50, 'testing for Ops. Manager', NULL, '2026-07-16 17:32:22', '2026-07-16 17:32:22'),
(9, 'ORD-20260716193222-669', 11, 13, 2, 'Pending', 1598.00, 'testing for Ops. Manager', NULL, '2026-07-16 17:32:22', '2026-07-16 17:32:22'),
(10, 'ORD-20260717040759-412', 11, 17, 1, 'Pending', 599.00, 'testing for duplicate order', NULL, '2026-07-17 02:07:59', '2026-07-17 02:07:59'),
(11, 'ORD-20260717040759-412', 11, 17, 1, 'Pending', 599.00, 'testing for duplicate order', NULL, '2026-07-17 02:07:59', '2026-07-17 02:07:59'),
(12, 'ORD-20260717051122-866', 11, 2, 2, 'Pending', 1798.00, 'testing', NULL, '2026-07-17 03:11:22', '2026-07-17 03:11:22'),
(13, 'ORD-20260721165100-753', 3, 13, 3, 'Pending', 2397.00, '', NULL, '2026-07-21 14:51:00', '2026-07-21 14:51:00'),
(14, 'ORD-20260721165100-753', 3, 22, 1, 'Pending', 989.00, '', NULL, '2026-07-21 14:51:00', '2026-07-21 14:51:00'),
(15, 'ORD-20260724070838-122', 3, 24, 7, 'Pending', 7343.00, 'new model', NULL, '2026-07-24 05:08:38', '2026-07-24 05:08:38'),
(16, 'ORD-20260802224240-642', 17, 14, 3, 'Pending', 3564.00, 'New supplier onboarding', NULL, '2026-08-02 20:42:40', '2026-08-02 20:42:40'),
(17, 'ORD-20260802224240-642', 17, 7, 3, 'Pending', 2097.00, 'New supplier onboarding', NULL, '2026-08-02 20:42:40', '2026-08-02 20:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `device_requests`
--

CREATE TABLE `device_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `rental_category` enum('school','tertiary','working') NOT NULL,
  `rental_duration` int(11) NOT NULL,
  `request_status` enum('Pending','Under Review','Approved','Collected','Returned','Rejected') DEFAULT 'Pending',
  `device_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_group_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_requests`
--

INSERT INTO `device_requests` (`request_id`, `user_id`, `device_id`, `quantity`, `payment_method`, `rental_category`, `rental_duration`, `request_status`, `device_price`, `created_at`, `request_group_id`) VALUES
(1, 2, 9, 1, 'Cash', 'tertiary', 3, 'Collected', 0.00, '2026-06-22 04:24:22', 'REQ-1782102262-2'),
(2, 2, 4, 1, 'Cash', 'tertiary', 3, 'Collected', 0.00, '2026-06-22 04:24:22', 'REQ-1782102262-2'),
(3, 5, 3, 3, 'Cash', 'working', 3, 'Rejected', 0.00, '2026-06-23 03:48:40', 'REQ-1782186520-5'),
(4, 5, 4, 1, 'Cash', 'working', 3, 'Returned', 0.00, '2026-06-23 06:33:21', 'REQ-1782196401-5'),
(5, 5, 16, 1, 'Cash', 'working', 3, 'Returned', 0.00, '2026-06-23 06:33:21', 'REQ-1782196401-5'),
(6, 5, 14, 1, 'Cash', 'working', 3, 'Returned', 0.00, '2026-06-23 06:33:21', 'REQ-1782196401-5'),
(7, 6, 2, 3, 'Online Banking', 'school', 4, 'Under Review', 0.00, '2026-06-23 14:42:16', 'REQ-1782225736-6'),
(8, 6, 7, 1, 'Online Banking', 'school', 4, 'Under Review', 0.00, '2026-06-23 14:42:16', 'REQ-1782225736-6'),
(9, 6, 12, 2, 'Online Banking', 'school', 4, 'Under Review', 0.00, '2026-06-23 14:42:16', 'REQ-1782225736-6'),
(10, 6, 5, 2, 'Online Banking', 'school', 4, 'Under Review', 0.00, '2026-06-23 14:42:16', 'REQ-1782225736-6'),
(11, 6, 4, 4, 'Online Banking', 'working', 4, 'Approved', 0.00, '2026-06-24 10:32:48', 'REQ-1782297168-6'),
(12, 2, 5, 3, 'E-Wallet', 'tertiary', 3, 'Pending', 0.00, '2026-06-29 18:52:39', 'REQ-1782759159-2'),
(13, 2, 8, 1, 'E-Wallet', 'tertiary', 3, 'Pending', 0.00, '2026-06-29 18:52:39', 'REQ-1782759159-2'),
(14, 2, 17, 3, 'Online Banking', 'school', 4, 'Pending', 0.00, '2026-06-29 19:09:19', 'REQ-1782760159-2'),
(15, 2, 9, 1, 'Online Banking', 'school', 4, 'Pending', 0.00, '2026-06-29 19:09:19', 'REQ-1782760159-2'),
(16, 2, 2, 1, 'E-Wallet', 'school', 4, 'Pending', 0.00, '2026-07-06 10:32:27', 'REQ-1783333947-2'),
(17, 2, 3, 2, 'E-Wallet', 'school', 4, 'Pending', 0.00, '2026-07-06 10:32:27', 'REQ-1783333947-2'),
(18, 2, 17, 1, 'E-Wallet', 'school', 4, 'Pending', 0.00, '2026-07-06 10:32:27', 'REQ-1783333947-2'),
(19, 2, 3, 1, 'E-Wallet', 'school', 4, 'Pending', 1599.00, '2026-07-07 03:59:04', 'REQ-1783396744-2'),
(20, 2, 18, 1, 'E-Wallet', 'school', 4, 'Pending', 650.00, '2026-07-07 03:59:04', 'REQ-1783396744-2'),
(21, 2, 14, 1, 'E-Wallet', 'school', 4, 'Pending', 1188.00, '2026-07-07 03:59:04', 'REQ-1783396744-2'),
(22, 2, 9, 1, 'E-Wallet', 'school', 4, 'Pending', 1889.00, '2026-07-07 03:59:04', 'REQ-1783396744-2'),
(23, 2, 13, 1, 'E-Wallet', 'school', 4, 'Pending', 799.00, '2026-07-07 03:59:04', 'REQ-1783396744-2'),
(24, 2, 9, 1, 'Online Banking', 'tertiary', 3, 'Pending', 1889.00, '2026-07-20 17:09:43', 'REQ-1784567383-2'),
(25, 2, 4, 2, 'Online Banking', 'tertiary', 3, 'Pending', 1429.00, '2026-07-20 17:09:43', 'REQ-1784567383-2'),
(26, 2, 18, 2, 'Online Banking', 'tertiary', 3, 'Pending', 650.00, '2026-07-20 17:09:43', 'REQ-1784567383-2'),
(27, 2, 24, 3, 'Online Banking', 'tertiary', 4, 'Pending', 1049.00, '2026-07-28 15:35:20', 'REQ-1785252920-2'),
(28, 2, 23, 3, 'Online Banking', 'tertiary', 4, 'Pending', 899.00, '2026-07-28 15:35:20', 'REQ-1785252920-2'),
(29, 2, 24, 2, 'Online Banking', 'school', 3, 'Under Review', 999.00, '2026-08-02 19:19:39', 'REQ-1785698379-2'),
(30, 2, 7, 1, 'Online Banking', 'school', 3, 'Under Review', 699.00, '2026-08-02 19:19:39', 'REQ-1785698379-2'),
(31, 2, 17, 1, 'Online Banking', 'school', 3, 'Under Review', 599.00, '2026-08-02 19:19:39', 'REQ-1785698379-2'),
(32, 18, 23, 2, 'Online Banking', 'working', 4, 'Pending', 899.00, '2026-08-02 20:35:03', 'REQ-1785702903-18'),
(33, 18, 22, 3, 'Online Banking', 'working', 4, 'Pending', 989.00, '2026-08-02 20:35:03', 'REQ-1785702903-18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `role`, `status`, `password`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Tester', 'tester@techbridge.com', 'admin', 'active', 'Jdk999ks#', 'profile_6a6fae662c3161.18144809.jpg', '2026-06-01 11:45:11', '2026-08-02 20:53:58'),
(2, 'John Doe', 'john.doe@techbridge.com', 'user', 'inactive', 'jde6767#', 'profile_6a68c662b67b46.45758940.jpg', '2026-06-02 01:09:59', '2026-07-28 15:10:26'),
(3, 'Techno Mobile Enterprise', 'sales@technomobile.com', 'supplier', '', 'teecm8800#', NULL, '2026-06-03 01:07:48', '2026-06-03 01:07:48'),
(4, 'Tech Support', 'techsupport@techbridge.com', 'admin', 'active', 'support99#', 'profile_6a20853d8714e0.66692496.jpg', '2026-06-03 03:35:05', '2026-06-03 03:49:17'),
(5, 'Freddie Boy', 'freddyboy26@gmail.com', 'user', 'active', 'frd3344#', NULL, '2026-06-22 17:14:45', '2026-07-22 01:29:49'),
(6, 'Kafka', 'kafa29@gmail.com', 'user', '', 'kfa1929$$', NULL, '2026-06-23 10:09:04', '2026-06-23 10:09:04'),
(7, 'Samsung Technologies', 'sales@samsung.com', 'supplier', 'active', 'aPBGlPfbDRd6', 'profile_6a3d4b2fad2ba7.86080311.webp', '2026-06-24 14:36:43', '2026-06-25 15:37:19'),
(9, 'BitHaus Technologies', 'sales@infohaus.com', 'supplier', '', 'ihs9912#', NULL, '2026-06-25 17:33:48', '2026-06-25 17:33:48'),
(11, 'TDH Enterprise', 'tdh2026@techbridge.com', 'supplier', '', 'tdh3344#$', 'profile_6a68b8413b00a5.23687839.jpg', '2026-07-14 12:10:06', '2026-07-28 14:10:09'),
(15, 'Varsenal', 'varsenal@gmail.com', 'user', 'active', 'sdfs43423', NULL, '2026-07-20 04:05:01', '2026-07-28 05:30:30'),
(17, 'Go Green Technologies', 'logistics@gogreentech.com', 'supplier', 'active', 'Grn6677#', 'profile_6a6fa7d41d9ac6.78501988.jpg', '2026-08-02 20:22:38', '2026-08-02 20:25:56'),
(18, 'Walker Junior', 'walkerjr@gmail.com', 'user', 'active', 'Wlk33445#', 'profile_6a6fac982d0070.58134156.jpg', '2026-08-02 20:33:51', '2026-08-02 20:46:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_cart` (`user_id`),
  ADD KEY `fk_cart_device_id` (`device_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`device_id`);

--
-- Indexes for table `device_orders`
--
ALTER TABLE `device_orders`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `device_requests`
--
ALTER TABLE `device_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `device_orders`
--
ALTER TABLE `device_orders`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `device_requests`
--
ALTER TABLE `device_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_cart_device_id` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`) ON DELETE CASCADE;

--
-- Constraints for table `device_orders`
--
ALTER TABLE `device_orders`
  ADD CONSTRAINT `device_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `device_orders_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
