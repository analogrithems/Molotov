<?php
	
	$new_activity = array(
		'id'=>'',
		'name'=>'Winter Luau',
		'short_description'=>"Winter in Hawaii is the perfect time four a Luau",
		'description'=>"The Winter Luau celebrates the the end of the year harvest in Hawaii.  The large feast is designed to fill you up and make sure no wasted crops are left over. The meal is cooked with chicken, pork, fish and all locally sourced vegetables.",
		'special_instructions'=>"Please note the event starts on time, please come 15min early to get your seats.",
		'language'=>'en_US',
		'currency'=>'USD',
		'status'=>'active',
		'variance'=>array(
			'guest'=>array(
				'name'=>'Guest Types',
				'model'=>'guestType',
				'multi'=>0,
				'required'=>1,
				'items'=>array(
					array(
						'id'=>'',
						'name'=>'Adult',
						'sku'=>'adult',
						'notes'=>'This guest type is used for adults',
						
					),
					array(
						'id'=>'',
						'name'=>'Child',
						'sku'=>'child',
						'notes'=>'This guest type is used for children',
						
					),
					array(
						'id'=>'',
						'name'=>'Child',
						'sku'=>'child',
						'notes'=>'This guest type is used for children',
						
					)
					
				)
			),
			'transportation'=>array(),
			'add_on'=>array(),
			'times'=>array(),
		),
		'meta'=>array(),
		'taxonomy'=>array(),
		'fees'=>array(),
		'company_id'=>3,
		'supplier_id'=>2,
		'product_id'=>'',
		'product_type'=>'activity',
		'created'=>'2013-04-01 00:00:00',
		'cfa'=>'1',
		'cutoff_hours'=>8,
		'cutoff_minutes'=>0,
		'book_until_end'=>0,
		'address'=> array(
			'address_1'=>'123 Rings St.',
			'address_2'=>'',
			'city'=>'Notorious',
			'state'=>'Ca',
			'postal'=>'90210',
			'country'=>'USA',
			'type'=>'activity'
		)
		
	);
	
	$new_food = array(
		'id'=>'',
		'name'=>'Cheese Burger Meal',
		'short_description'=>"Large Cheeseburger with your choice of sides",
		'description'=>"Large Cheeseburger with your choice of sides",
		'special_instructions'=>"Do to health concerns we do not cook beef rare.",
		'language'=>'en_US',
		'currency'=>'USD',
		'status'=>'active',
		'variance'=>array(
			'sides'=>array(
				'name'=>'Sides',
				'model'=>'foodSides',
				'multi'=>0,
				'required'=>1,
				'items'=>array(
					array(
						'id'=>'',
						'name'=>'French Fires',
						'sku'=>'fires',
						'notes'=>'Home style fires',
						'amount'=>'5.99'
					),
					array(
						'id'=>'',
						'name'=>'Side Salad',
						'sku'=>'side-salad',
						'notes'=>"Comes with a side of ranch, other dressing's include Italian and thousand island dressings",
						'amount'=>'4.99'
					),
					array(
						'id'=>'',
						'name'=>'Baked Potato',
						'sku'=>'potato',
						'notes'=>"Baked potato with cheese, chives and sour cream.",
						'amount'=>'6.99'
					)
				)
			),
		),
		'meta'=>array(
			'rank'=>10,
			'flavor'=>'western'
		),
		'taxonomy'=>array(
			array(
				'taxonomy'=>'tag',
				'term'=>'Burger',
				'language'=>'en_US'
			),
			array(
				'taxonomy'=>'tag',
				'term'=>'Fast Good'
			),
		),
		'fees'=>array(
			array(
				'id'=>'',
				'name'=>'Service Fee',
				'percent'=>'5'
			)
		),
		'company_id'=>3,
		'supplier_id'=>2,
		'product_id'=>'',
		'product_type'=>'food',
		'created'=>'2013-04-01 00:00:00'
		
	);
	