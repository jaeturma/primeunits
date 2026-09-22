<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $fuelTypes = ['Gasoline', 'Diesel', 'LPG', 'BEV', 'HEV', 'PHEV', 'eREV', 'FCEV'];
        $transmissions = ['Manual', 'Automatic', 'CVT', 'DCT', 'Semi-Automatic'];
        $colors = ['White', 'Black', 'Silver', 'Gray', 'Red', 'Blue', 'Green', 'Yellow', 'Orange', 'Brown', 'Gold', 'Other'];
        $bodyTypes = ['Sedan', 'SUV', 'Hatchback', 'MPV', 'Van', 'Pickup', 'Convertible', 'Coupe', 'Crossover', 'Wagon', 'Sports Car', 'Exotic', 'Armored & Security Vehicle'];
        $driveTypes = ['FWD', 'RWD', 'AWD', '4WD'];
        $engineSizes = ['660cc', '1.0L', '1.3L', '1.5L', '1.6L', '1.8L', '2.0L', '2.4L', '2.5L', '3.0L', '3.5L', '4.0L', '5.0L', 'Other'];

        $categories = [

            // ───── CARS ─────
            [
                'name' => 'Cars',
                'slug' => 'cars',
                'icon' => 'car',
                'sort_order' => 1,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'body_type', 'label' => 'Body Type', 'type' => 'select', 'options' => $bodyTypes, 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'options' => $transmissions, 'required' => true, 'sort_order' => 2, 'is_searchable' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'options' => $fuelTypes, 'required' => true, 'sort_order' => 3, 'is_searchable' => true],
                    ['name' => 'engine_size', 'label' => 'Engine Displacement', 'type' => 'select', 'options' => $engineSizes, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'drive_type', 'label' => 'Drive Type', 'type' => 'select', 'options' => $driveTypes, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'mileage', 'label' => 'Mileage (km)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => true],
                    ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => $colors, 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                    ['name' => 'doors', 'label' => 'Doors', 'type' => 'select', 'options' => ['2', '4', '5'], 'required' => false, 'sort_order' => 8, 'is_searchable' => false],
                    ['name' => 'seating_capacity', 'label' => 'Seating Capacity', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 9, 'is_searchable' => false],
                    ['name' => 'plate_number', 'label' => 'Plate Number', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 10, 'is_searchable' => false],
                    ['name' => 'or_cr', 'label' => 'OR/CR Status', 'type' => 'select', 'options' => ['Complete', 'Incomplete', 'Expired'], 'required' => false, 'sort_order' => 11, 'is_searchable' => false],
                ],
            ],

            // ───── MOTORCYCLES ─────
            [
                'name' => 'Motorcycles',
                'slug' => 'motorcycles',
                'icon' => 'bike',
                'sort_order' => 2,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'motorcycle_type', 'label' => 'Motorcycle Type', 'type' => 'select', 'options' => ['Sports', 'Adventure / Touring', 'Big Bike', 'Naked / Street', 'Scooter', 'Underbone', 'Moto3 / Mini Bike', 'Electric Motorcycle', 'Sidecar', 'Premium Custom Motorcycle', 'Collector Chopper', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'options' => ['Gasoline', 'BEV', 'HEV', 'PHEV', 'eREV', 'FCEV'], 'required' => true, 'sort_order' => 2, 'is_searchable' => true],
                    ['name' => 'displacement', 'label' => 'Displacement (cc)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'mileage', 'label' => 'Mileage (km)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => true],
                    ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => $colors, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'options' => ['Manual', 'Automatic / CVT', 'Semi-Automatic'], 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'or_cr', 'label' => 'OR/CR Status', 'type' => 'select', 'options' => ['Complete', 'Incomplete', 'Expired'], 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                ],
            ],

            // ───── COMMERCIAL VEHICLES ─────
            [
                'name' => 'Commercial Vehicles',
                'slug' => 'commercial-vehicles',
                'icon' => 'truck',
                'sort_order' => 3,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'vehicle_type', 'label' => 'Vehicle Type', 'type' => 'select', 'options' => ['Truck', 'Van', 'Minibus', 'Bus', 'Jeepney', 'UV Express', 'Tricycle / Sidecar', 'Multicab / Multicab Van', 'Trailer / Flatbed', 'Refrigerated Van', 'Dump Truck', 'Tanker', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'options' => $fuelTypes, 'required' => true, 'sort_order' => 2, 'is_searchable' => true],
                    ['name' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'options' => $transmissions, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'mileage', 'label' => 'Mileage (km)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => true],
                    ['name' => 'payload_capacity', 'label' => 'Payload Capacity (tons)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'gross_vehicle_weight', 'label' => 'Gross Vehicle Weight (kg)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'seating_capacity', 'label' => 'Passenger Capacity', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                    ['name' => 'or_cr', 'label' => 'OR/CR Status', 'type' => 'select', 'options' => ['Complete', 'Incomplete', 'Expired'], 'required' => false, 'sort_order' => 8, 'is_searchable' => false],
                    ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => $colors, 'required' => false, 'sort_order' => 9, 'is_searchable' => false],
                ],
            ],

            // ───── AGRICULTURAL EQUIPMENT ─────
            [
                'name' => 'Agricultural Equipment',
                'slug' => 'agricultural-equipment',
                'icon' => 'tractor',
                'sort_order' => 4,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'equipment_type', 'label' => 'Equipment Type', 'type' => 'select', 'options' => ['Tractor', 'Hand Tractor', 'Rice Transplanter', 'Rice Combine Harvester', 'Agricultural Drone', 'Thresher', 'Sprayer', 'Irrigation Pump', 'Corn Sheller', 'Rice Mill', 'Seeder', 'Plow', 'Rotavator', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'options' => ['Diesel', 'Gasoline', 'BEV', 'Other'], 'required' => false, 'sort_order' => 2, 'is_searchable' => false],
                    ['name' => 'horsepower', 'label' => 'Horsepower (HP)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'usage_hours', 'label' => 'Usage Hours', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'cutting_width', 'label' => 'Cutting Width (m)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'engine_brand', 'label' => 'Engine Brand', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'supported_crops', 'label' => 'Supported Crops', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 7, 'is_searchable' => true],
                    ['name' => 'grain_tank_capacity', 'label' => 'Grain Tank Capacity (L)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 8, 'is_searchable' => false],
                    ['name' => 'operating_capacity_ha_per_hour', 'label' => 'Operating / Coverage Capacity (ha per hour)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 9, 'is_searchable' => true],
                    ['name' => 'hour_meter_reading', 'label' => 'Hour-Meter Reading (hrs)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 10, 'is_searchable' => false],
                    ['name' => 'intended_agricultural_uses', 'label' => 'Intended Agricultural Uses', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 11, 'is_searchable' => true],
                    ['name' => 'maximum_payload_kg', 'label' => 'Maximum Payload (kg)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 12, 'is_searchable' => false],
                    ['name' => 'spray_tank_capacity_liters', 'label' => 'Spray Tank Capacity (L)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 13, 'is_searchable' => false],
                    ['name' => 'flight_time_minutes', 'label' => 'Flight Time per Charge (min)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 14, 'is_searchable' => false],
                    ['name' => 'battery_count', 'label' => 'Number of Batteries', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 15, 'is_searchable' => false],
                    ['name' => 'charging_equipment_included', 'label' => 'Charging Equipment Included', 'type' => 'select', 'options' => ['Yes', 'No'], 'required' => false, 'sort_order' => 16, 'is_searchable' => false],
                    ['name' => 'positioning_capability', 'label' => 'GPS / RTK / Mapping Capability', 'type' => 'select', 'options' => ['GPS Only', 'GPS + RTK', 'GPS + RTK + Mapping', 'None'], 'required' => false, 'sort_order' => 17, 'is_searchable' => true],
                    ['name' => 'camera_sensor_type', 'label' => 'Camera or Sensor Type', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 18, 'is_searchable' => false],
                    ['name' => 'controller_included', 'label' => 'Controller Included', 'type' => 'select', 'options' => ['Yes', 'No'], 'required' => false, 'sort_order' => 19, 'is_searchable' => false],
                    ['name' => 'service_coverage_area', 'label' => 'Service Coverage Area', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 20, 'is_searchable' => false],
                ],
            ],

            // ───── HEAVY EQUIPMENT ─────
            [
                'name' => 'Heavy Equipment',
                'slug' => 'heavy-equipment',
                'icon' => 'construction',
                'sort_order' => 5,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'equipment_type', 'label' => 'Equipment Type', 'type' => 'select', 'options' => ['Excavator', 'Bulldozer', 'Wheel Loader', 'Backhoe', 'Motor Grader', 'Crane', 'Forklift', 'Compactor / Roller', 'Concrete Mixer', 'Generator Set', 'Air Compressor', 'Pile Driver', 'Pavement Roller', 'Tower Crane', 'Boom Lift', 'Scissor Lift', 'Skid Steer Loader', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel Type', 'type' => 'select', 'options' => ['Diesel', 'Gasoline', 'BEV', 'Other'], 'required' => false, 'sort_order' => 2, 'is_searchable' => false],
                    ['name' => 'engine_power', 'label' => 'Engine Power (HP)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'operating_weight', 'label' => 'Operating Weight (tons)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'bucket_capacity', 'label' => 'Bucket Capacity (m³)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'usage_hours', 'label' => 'Usage Hours', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'lifting_capacity', 'label' => 'Lifting Capacity (tons)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                ],
            ],

            // ───── ELECTRIC VEHICLES ─────
            [
                'name' => 'Electric Vehicles',
                'slug' => 'electric-vehicles',
                'icon' => 'zap',
                'sort_order' => 6,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'ev_type', 'label' => 'EV Type', 'type' => 'select', 'options' => ['Electric Car (BEV)', 'Hybrid (HEV)', 'Plug-in Hybrid (PHEV)', 'Electric Motorcycle', 'Electric Scooter / E-Bike', 'Electric Tricycle', 'Electric Bus', 'Electric Truck', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'battery_capacity', 'label' => 'Battery Capacity (kWh)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 2, 'is_searchable' => false],
                    ['name' => 'range_km', 'label' => 'Range (km)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'motor_power', 'label' => 'Motor Power (kW)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'charging_type', 'label' => 'Charging Type', 'type' => 'select', 'options' => ['AC Level 1', 'AC Level 2', 'DC Fast Charge', 'Wireless', 'Multiple'], 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'mileage', 'label' => 'Mileage (km)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => true],
                    ['name' => 'transmission', 'label' => 'Transmission', 'type' => 'select', 'options' => ['Single Speed', 'Automatic', 'CVT'], 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                    ['name' => 'color', 'label' => 'Color', 'type' => 'select', 'options' => $colors, 'required' => false, 'sort_order' => 8, 'is_searchable' => false],
                    ['name' => 'battery_health', 'label' => 'Battery Health (%)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 9, 'is_searchable' => false],
                    ['name' => 'or_cr', 'label' => 'OR/CR Status', 'type' => 'select', 'options' => ['Complete', 'Incomplete', 'Expired', 'Not Required'], 'required' => false, 'sort_order' => 10, 'is_searchable' => false],
                ],
            ],

            // ───── OTHER UNITS ─────
            [
                'name' => 'Other Units',
                'slug' => 'other-units',
                'icon' => 'package',
                'sort_order' => 7,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'unit_type', 'label' => 'Unit Type', 'type' => 'select', 'options' => ['Boat / Watercraft', 'Generator', 'Industrial Equipment', 'Medical Equipment', 'Office Equipment', 'Power Tools', 'Pumps', 'Printing Equipment', 'Other'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'fuel_type', 'label' => 'Fuel / Power Source', 'type' => 'select', 'options' => ['Gasoline', 'Diesel', 'BEV', 'Solar', 'Manual', 'Other'], 'required' => false, 'sort_order' => 2, 'is_searchable' => false],
                    ['name' => 'power_output', 'label' => 'Power Output', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'capacity', 'label' => 'Capacity', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'dimensions', 'label' => 'Dimensions (LxWxH)', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'usage_hours', 'label' => 'Usage Hours', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                ],
            ],

            // ───── WATERCRAFT & MARINE VESSELS (Silver baseline, Gold on approval) ─────
            [
                'name' => 'Watercraft & Marine Vessels',
                'slug' => 'watercraft-marine-vessels',
                'icon' => 'anchor',
                'sort_order' => 8,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'vessel_type', 'label' => 'Vessel Type', 'type' => 'select', 'options' => ['Personal Watercraft (Jet Skis)', 'Day Boat / Speedboat', 'Sailing Yacht', 'Motor Yacht', 'Superyacht', 'Large Leisure Vessel', 'Commercial Vessel'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'length_overall_m', 'label' => 'Length Overall (m)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 2, 'is_searchable' => true],
                    ['name' => 'beam_m', 'label' => 'Beam (m)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'draft_m', 'label' => 'Draft (m)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'gross_tonnage', 'label' => 'Gross Tonnage', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'passenger_capacity', 'label' => 'Passenger Capacity', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'cabin_count', 'label' => 'Cabin Count', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                    ['name' => 'crew_capacity', 'label' => 'Crew Capacity', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 8, 'is_searchable' => false],
                    ['name' => 'hull_type', 'label' => 'Hull Type', 'type' => 'select', 'options' => ['Monohull', 'Catamaran', 'Trimaran', 'Planing', 'Displacement', 'Other'], 'required' => false, 'sort_order' => 9, 'is_searchable' => false],
                    ['name' => 'vessel_use', 'label' => 'Vessel Use', 'type' => 'select', 'options' => ['Recreational', 'Charter', 'Commercial'], 'required' => false, 'sort_order' => 10, 'is_searchable' => true],
                    ['name' => 'flag_jurisdiction', 'label' => 'Flag / Jurisdiction', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 11, 'is_searchable' => false],
                ],
            ],

            // ───── AIRCRAFT (Gold: private, professionally reviewed marketplace) ─────
            [
                'name' => 'Aircraft',
                'slug' => 'aircraft',
                'icon' => 'plane',
                'sort_order' => 9,
                'is_active' => true,
                'commission_rate' => 2.00,
                'fields' => [
                    ['name' => 'aircraft_type', 'label' => 'Aircraft Type', 'type' => 'select', 'options' => ['Light Aircraft', 'Turboprop Aircraft', 'Private Jet', 'Helicopter'], 'required' => true, 'sort_order' => 1, 'is_searchable' => true, 'is_classification' => true],
                    ['name' => 'total_time_hours', 'label' => 'Total Time (hours)', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 2, 'is_searchable' => false],
                    ['name' => 'total_cycles', 'label' => 'Total Cycles', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 3, 'is_searchable' => false],
                    ['name' => 'engine_hours', 'label' => 'Engine Hours Since Overhaul', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 4, 'is_searchable' => false],
                    ['name' => 'seating_capacity', 'label' => 'Seating Capacity', 'type' => 'number', 'options' => null, 'required' => false, 'sort_order' => 5, 'is_searchable' => false],
                    ['name' => 'airworthiness_status', 'label' => 'Airworthiness Status', 'type' => 'select', 'options' => ['Current', 'Due for Inspection', 'Not Currently Airworthy'], 'required' => false, 'sort_order' => 6, 'is_searchable' => false],
                    ['name' => 'home_base', 'label' => 'Home Base / Registration Country', 'type' => 'text', 'options' => null, 'required' => false, 'sort_order' => 7, 'is_searchable' => false],
                ],
            ],
        ];

        foreach ($categories as $data) {
            $fields = $data['fields'];
            unset($data['fields']);

            $category = Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            foreach ($fields as $field) {
                $category->specFields()->updateOrCreate(
                    ['name' => $field['name']],
                    $field,
                );
            }
        }
    }
}
