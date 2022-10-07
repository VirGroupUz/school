	<?php
	error_reporting(0);
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json'); 

	$data = ['ok'=>false, 'code'=>null, 'message'=>null, 'result'=>[]];
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		require './config/config.php';
		$db = new dbmysqli;
    	$db->dbConnect();

		extract($_REQUEST);
		$action = strtolower(trim(getenv('ORIG_PATH_INFO') ? : getenv('PATH_INFO'), '/'));
		if ($action == 'managerlogin') {
			if (isset($login) && isset($password)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'login'=>$login,
                        'cn'=>'='
                    ],
                    [
                        'pass_word'=>$password,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	$manager = mysqli_fetch_assoc($manager);
                	if ($manager['pass_word'] == $password) {
                		$data['ok'] = true;
                		$data['code'] = 200;
                		$data['message'] = 'Loggid in successfully';
                		foreach ($manager as $key => $value) $data['result'][$key] = $value;
                	}else{
                		$data['code'] = 403;
                		$data['message'] = 'password is invalid';
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'login or password is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'login and password is required';
			}
		}else if($action == 'managereditprofile'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	$manager = mysqli_fetch_assoc($manager);
                	$first_name = $first_name ? $first_name : $manager['first_name'];
                	$last_name = $last_name ? $last_name : $manager['last_name'];
                	$login = $login ? $login : $manager['login'];
                	$password = $password ? $password : $manager['pass_word'];
                	$email = $email ? $email : $manager['email'];

                	$update = $db->update('manager',[
                        'first_name'=>$first_name,
                        'last_name'=>$last_name,
                        'login'=>$login,
                        'pass_word'=>$password,
                        'email'=>$email,
                    ],[
                        'token'=>$token,
                        'cn'=>'='
                    ]);
                    if ($update) {
                    	$data['ok'] = true;
                    	$data['code'] = 200;
                    	$data['message'] = "Manager profile edited successfully";
                    	$manager = mysqli_fetch_assoc($db->selectWhere('manager',[
		                    [
		                        'token'=>$token,
		                        'cn'=>'='
		                    ],
		                ]));
		                foreach ($manager as $key => $value) $data['result'][$key] = $value;
                    }else{
                    	$data['code'] = 500;
                    	$data['message'] = "Set interval error";
                    }
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'addroomtoschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	if (isset($name)) {
                		$student_count = $student_count ? $student_count : 10;
                		$ins = $db->insertInto('rooms',[
                			'name'=>$name,
                			'student_count'=>$student_count
                		]);

                		$data['code'] = 200;
                		$data['message'] = 'Room added successfully';

                		if (!$ins) {
                			$data['code'] = 500;
                			$data['message'] = 'Insert error: 500 set interval error';
                		}
                		$data['ok'] = true;
                		$rooms = $db->selectWhere('rooms',[
		                    [
		                        'id'=>0,
		                        'cn'=>'>'
		                    ],
		                ]);
		                foreach ($rooms as $key => $value) $data['result'][$key] = $value;
                	}else{
                		$data['code'] = 402;
                		$data['message'] = 'room name is required';
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'getroomfromschool'){
        	$rooms = $db->selectWhere('rooms',[
                [
                    'id'=>0,
                    'cn'=>'>'
                ],
            ]);
            $data['ok'] = true;
            if ($rooms->num_rows) {
            	$data['code'] = 200;
            	$data['message'] = "Rooms count: " . $rooms->num_rows;
            	foreach ($rooms as $key => $value) $data['result'][] = $value;
            }else{
            	$data['code'] = 405;
            	$data['message'] = "The rooms are empty from the school";
            }
		}else if($action == 'deleteroomfromschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	if (isset($id)) {
                		$delete = $db->delete('rooms',[
							[
								'id'=>$id,
								'cn'=>'='
							]
						]);
						$data['ok'] = true;
						if ($delete) {
							$data['code'] = 200;
							$data['message'] = 'Room is deleted successfully';
						}
						$rooms = $db->selectWhere('rooms',[
			                [
			                    'id'=>0,
			                    'cn'=>'>'
			                ],
			            ]);
			            if ($rooms->num_rows) {
			            	$data['message'] = $data['message'] . " Rooms count: " . $rooms->num_rows;
			            	foreach ($rooms as $key => $value) $data['result'][] = $value;
			            }else{
			            	$data['code'] = 405;
			            	$data['message'] = $data['message'] . " The rooms are empty from the school";
			            }
                	}else{
                		$data['code'] = 402;
                		$data['message'] = "Room id is required";
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'adddirectiontoschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	if (isset($name)) {
                		$monthly_price = $monthly_price ? $monthly_price : 400000;
                		$duration = $duration ? $duration : 6;
                		$ins = $db->insertInto('directions',[
                			'name'=>$name,
                			'monthly_price'=>$monthly_price,
                			'duration'=>$duration
                		]);

                		$data['code'] = 200;
                		$data['message'] = 'Direction added successfully';

                		if (!$ins) {
                			$data['code'] = 500;
                			$data['message'] = 'Insert error: 500 set interval error';
                		}
                		$data['ok'] = true;
                		$rooms = $db->selectWhere('directions',[
		                    [
		                        'id'=>0,
		                        'cn'=>'>'
		                    ],
		                ]);
		                foreach ($rooms as $key => $value) $data['result'][$key] = $value;
                	}else{
                		$data['code'] = 402;
                		$data['message'] = 'direction name is required';
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'getdirectionsfromschool'){
        	$rooms = $db->selectWhere('directions',[
                [
                    'id'=>0,
                    'cn'=>'>'
                ],
            ]);
            $data['ok'] = true;
            if ($rooms->num_rows) {
            	$data['code'] = 200;
            	$data['message'] = "Directions count: " . $rooms->num_rows;
            	foreach ($rooms as $key => $value) $data['result'][] = $value;
            }else{
            	$data['code'] = 405;
            	$data['message'] = "The directions are empty from the school";
            }
		}else if($action == 'deletedirectionfromschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	if (isset($id)) {
                		$delete = $db->delete('directions',[
							[
								'id'=>$id,
								'cn'=>'='
							]
						]);
						$data['ok'] = true;
						if ($delete) {
							$data['code'] = 200;
							$data['message'] = 'Direction is deleted successfully';
						}
						$rooms = $db->selectWhere('directions',[
			                [
			                    'id'=>0,
			                    'cn'=>'>'
			                ],
			            ]);
			            if ($rooms->num_rows) {
			            	$data['message'] = $data['message'] . " directions count: " . $rooms->num_rows;
			            	foreach ($rooms as $key => $value) $data['result'][] = $value;
			            }else{
			            	$data['code'] = 405;
			            	$data['message'] = $data['message'] . " The directions are empty from the school";
			            }
                	}else{
                		$data['code'] = 402;
                		$data['message'] = "Directions id is required";
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'registration'){
        	if (isset($name) && isset($phone) && isset($direction_id)) {
        		$direction = $db->selectWhere('directions', [
        			[
        				'id'=>$direction_id,
        				'cn'=>'='
        			]
        		]);
        		if ($direction->num_rows) {
        			$direction_fetch = mysqli_fetch_assoc($direction);
        			$ins = $db->insertInto('registred',[
	        			'name'=>$name,
	        			'phone'=>$phone,
	        			'direction'=>$direction_fetch['name'],
	        			'registred_date'=>strtotime('now'),
	        		]);

	        		$data['code'] = 200;
	        		$data['message'] = 'You have successfully registered';

	        		if (!$ins) {
	        			$data['code'] = 500;
	        			$data['message'] = 'Insert error: 500 set interval error';
	        		}

	        		$data['ok'] = true;
	        		$students = $db->selectWhere('registred',[
	                    [
	                        'id'=>0,
	                        'cn'=>'>'
	                    ],
	                ]);
	                foreach ($students as $key => $value) $data['result'][$key] = $value;
        		}else{
        			$data['code'] = 403;
        			$data['message'] = 'direction_id is invalid';
        		}
        	}else{
        		$data['code'] = 402;
        		$data['message'] = 'name, phone, direction_id are required';
        	}
		}else if($action == 'getregistration'){
			$students = $db->selectWhere('registred',[
				[
					'id'=>0,
					'cn'=>'>'
				],
			]);
			$data['ok'] = true;
			if ($students->num_rows) {
            	$data['code'] = 200;
            	$data['message'] = "Students count: " . $students->num_rows;
            	foreach ($students as $key => $value) $data['result'][] = $value;
            }else{
            	$data['code'] = 405;
            	$data['message'] = "There are no new students";
            }
		}else if($action == 'confirmationofreceipt'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
				if ($manager->num_rows) {
					if (isset($id)) {
						$student = $db->selectWhere('registred',[
							[
								'id'=>$id,
								'cn'=>'='
							],
						]);
						if ($student->num_rows) {
							$update = $db->update('registred',[
		                        'confirm'=>1,
		                    ],[
		                        'id'=>$id,
		                        'cn'=>'='
		                    ]);
							$data['ok'] = true;
							if ($update) {
								$data['code'] = 200;
								$data['message'] = 'The student was accepted';
							}
							$students = $db->selectWhere('registred',[
				                [
				                    'id'=>0,
				                    'cn'=>'>'
				                ],
				            ]);
				            if ($students->num_rows) {
				            	$data['message'] = $data['message'] . " students count: " . $students->num_rows;
				            	foreach ($students as $key => $value) $data['result'][] = $value;
				            }else{
				            	$data['code'] = 405;
				            	$data['message'] = $data['message'] . " There are no new students";
				            }
						}else{
							$data['code'] = 403;
	            			$data['message'] = 'student id is invalid';
						}
					}else{
						$data['code'] = 403;
            			$data['message'] = 'student id is required';
					}
				}else{
					$data['code'] = 403;
                	$data['message'] = 'token is invalid';
				}
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'addteachertoschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
                if ($manager->num_rows) {
                	if (isset($name) && isset($direction_id) && isset($phone) && isset($login) && isset($password)) {
                		$direction = $db->selectWhere('directions',[
                			[
                				'id'=>$direction_id,
                				'cn'=>'='
                			],
                		]);
                		if ($direction->num_rows) {
                			$teachers = $db->selectWhere('teachers',[
							    [
							        'login'=>$login,
							        'cn'=>'='
							    ],
							]);
							if ($teachers->num_rows) {
								$data['code'] = 403;
                				$data['message'] = 'Such a login already exists';
							}else{
								$direction_fetch = mysqli_fetch_assoc($direction);
	                			$ins = $db->insertInto('teachers',[
				        			'name'=>$name,
				        			'direction'=>$direction_id,
				        			'techarAddedDate'=>strtotime('now'),
				        			'login'=>$login,
				        			'password'=>$password,
				        			'phone'=>$phone,
				        			'del'=>0,
				        			'token'=>md5(uniqid($login))
				        		]);

				        		$data['code'] = 200;
				        		$data['message'] = 'The teacher was successfully logged in';

				        		if (!$ins) {
				        			$data['code'] = 500;
				        			$data['message'] = 'Insert error: 500 set interval error';
				        		}

				        		$data['ok'] = true;
				        		$teachers = $db->selectWhere('teachers',[
				                    [
				                        'id'=>0,
				                        'cn'=>'>'
				                    ],
				                ]);
				                foreach ($teachers as $key => $value) $data['result'][$key] = $value;
							}
                		}else{
                			$data['code'] = 403;
                			$data['message'] = 'direction_id is invalid';
                		}
                	}else{
                		$data['code'] = 402;
                		$data['message'] = 'name, direction_id, phone, login, password are required';
                	}
                }else{
                	$data['code'] = 403;
                	$data['message'] = 'token is invalid';
                }
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'teachereditprofile') {
			if (isset($token)) {
				$teacher = $db->selectWhere('teachers',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($teacher->num_rows) {
			    	if (isset($name) || isset($direction) || isset($login) || isset($password) || isset($phone)) {
			    		$teacher_fetch = mysqli_fetch_assoc($teacher);
		    			$bool = true;
			    		$name = $name ? $name : $teacher_fetch['name'];
			    		$direction = $direction ? $direction : $teacher_fetch['direction'];
			    		$login = $login ? $login : $teacher_fetch['login'];
			    		$password = $password ? $password : $teacher_fetch['password'];
			    		$phone = $phone ? $phone : $teacher_fetch['phone'];
				    	if ($bool) {
							$update = $db->update('teachers',[
							    'name'=>$name,
							    'direction'=>$direction,
							    'login'=>$login,
							    'password'=>$password,
							    'phone'=>$phone,
							],[
							    'token'=>$token,
							    'cn'=>'='
							]);
							$data['ok'] = true;
							if ($update) {
							    $data['code'] = 200;
							    $data['message'] = 'Teacher data has been changed successfully.';
							}
							$groups = $db->selectWhere('teachers',[
							    [
							        'id'=>0,
							        'cn'=>'>'
							    ],
							]);
							if ($groups->num_rows) {
							    $data['message'] = $data['message'] . " teachers count: " . $groups->num_rows;
							    foreach ($groups as $key => $value) $data['result'][] = $value;
							}else{
							    $data['code'] = 405;
							    $data['message'] = $data['message'] . " There are no teachers";
							}
						}
		    		}else{
			    		$data['code'] = 403;
						$data['message'] = 'One of these is required (name, direction, login, password, phone)';
			    	}
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
		    }else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else if($action == 'getteacherfromschool'){
			$teachers = $db->selectWhere('teachers',[
				[
					'id'=>0,
					'cn'=>'>'
				],
			]);
			$data['ok'] = true;
			if ($teachers->num_rows) {
            	$data['code'] = 200;
            	$data['message'] = "Teachers count: " . $teachers->num_rows;
            	foreach ($teachers as $key => $value) {
            		$directions = $db->selectWhere('directions',[
						[
							'id'=>$value['direction'],
							'cn'=>'='
						],
					]); 
            		$value[] = mysqli_fetch_assoc($directions);  
            		$data['result'][] = $value;
            	}
            }else{
            	$data['code'] = 405;
            	$data['message'] = "There are no teachers";
            }
		}else if($action == 'deleteteacherfromschool'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
                    [
                        'token'=>$token,
                        'cn'=>'='
                    ],
                ]);
				if ($manager->num_rows) {
					if (isset($id)) {
						$teacher = $db->selectWhere('teachers',[
							[
								'id'=>$id,
								'cn'=>'='
							],
						]);
						if ($teacher->num_rows) {
							$update = $db->update('teachers',[
		                        'del'=>1,
		                    ],[
		                        'id'=>$id,
		                        'cn'=>'='
		                    ]);
							$data['ok'] = true;
							if ($update) {
								$data['code'] = 200;
								$data['message'] = 'The teacher has been deleted';
							}
							$teachers = $db->selectWhere('teachers',[
				                [
				                    'id'=>0,
				                    'cn'=>'>'
				                ],
				            ]);
				            if ($teachers->num_rows) {
				            	$data['message'] = $data['message'] . " teachers count: " . $teachers->num_rows;
				            	foreach ($teachers as $key => $value) $data['result'][] = $value;
				            }else{
				            	$data['code'] = 405;
				            	$data['message'] = $data['message'] . " There are no teachers";
				            }
						}else{
							$data['code'] = 403;
	            			$data['message'] = 'teacher id is invalid';
						}
					}else{
						$data['code'] = 403;
            			$data['message'] = 'teacher id is required';
					}
				}else{
					$data['code'] = 403;
                	$data['message'] = 'token is invalid';
				}
			}else{
				$data['code'] = 402;
                $data['message'] = 'token is required';
			}
		}else if($action == 'creategroup'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($manager->num_rows) {
			    	if (isset($group_name) && isset($direction_id) && isset($teacher_id) && isset($room_id) && isset($start_time) && isset($end_time)) {
			    		$bool = true;
			    		if (isset($direction_id)) {
			    			$direction = $db->selectWhere('directions',[
							    [
							        'id'=>$direction_id,
							        'cn'=>'='
							    ],
							]);
							if ($direction->num_rows) {
								
							}else{
								$bool = false;
								$data['code'] = 403;
			    				$data['message'] = 'direction_id is invalid';
							}
			    		}
			    		if (isset($teacher_id)) {
			    			$teacher = $db->selectWhere('teachers',[
							    [
							        'id'=>$teacher_id,
							        'cn'=>'='
							    ],
							]);
							if ($teacher->num_rows) {
								
							}else{
								$bool = false;
								$data['code'] = 403;
			    				$data['message'] = 'teacher_id is invalid';
							}
			    		}
			    		if (isset($room_id)) {
			    			$room = $db->selectWhere('rooms',[
							    [
							        'id'=>$room_id,
							        'cn'=>'='
							    ],
							]);
							if ($room->num_rows) {
								
							}else{
								$bool = false;
								$data['code'] = 403;
			    				$data['message'] = 'room_id is invalid';
							}
			    		}
				    	if ($bool) {
				    		$ins = $db->insertInto('groups',[
							    'group_name'=>$group_name,
							    'direction_id'=>$direction_id,
							    'teacher_id'=>$teacher_id,
							    'room_id'=>$room_id,
							    'start_time'=>$start_time,
							    'end_time'=>$end_time
							]);

							$data['code'] = 200;
							$data['message'] = 'Group created successfully';

							if (!$ins) {
							    $data['code'] = 500;
							    $data['message'] = 'Insert error: 500 set interval error';
							}

							$data['ok'] = true;
							$groups = $db->selectWhere('groups',[
							    [
							        'id'=>0,
							        'cn'=>'>'
							    ],
							]);
							foreach ($groups as $key => $value) $data['result'][$key] = $value;
				    	}
			    	}else{
			    		$data['code'] = 403;
						$data['message'] = 'group_name, direction_id, teacher_id, room_id, start_time, end_time are required';
			    	}
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
			}else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else if($action == 'getgroups'){
			$groups = $db->selectWhere('groups',[
			    [
			        'id'=>0,
			        'cn'=>'>'
			    ],
			]);
			$data['ok'] = true;
			if ($groups->num_rows) {
			    $data['code'] = 200;
			    $data['message'] = "Groups count: " . $groups->num_rows;
			    foreach ($groups as $key => $value) $data['result'][] = $value;
			}else{
			    $data['code'] = 405;
			    $data['message'] = "There are no new groups";
			}	
		}else if($action == 'deletegroup'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($manager->num_rows) {
			    	if (isset($group_id)) {
			    		$group = $db->selectWhere('groups',[
						    [
						        'id'=>$group_id,
						        'cn'=>'='
						    ],
						]);
			    		if ($group->num_rows) {
			    			$update = $db->update('groups',[
							    'del'=>1,
							],[
							    'id'=>$group_id,
							    'cn'=>'='
							]);
							$data['ok'] = true;
							if ($update) {
							    $data['code'] = 200;
							    $data['message'] = 'The group has been deleted';
							}
							$groups = $db->selectWhere('groups',[
							    [
							        'id'=>0,
							        'cn'=>'>'
							    ],
							]);
							if ($groups->num_rows) {
							    $data['message'] = $data['message'] . " groups count: " . $groups->num_rows;
							    foreach ($groups as $key => $value) $data['result'][] = $value;
							}else{
							    $data['code'] = 405;
							    $data['message'] = $data['message'] . " There are no groups";
							}	
			    		}else{
			    			$data['code'] = 403;
			    			$data['message'] = 'group_id is invalid';
			    		}
			    	}else{
			    		$data['code'] = 403;
						$data['message'] = 'group_id is required';
			    	}
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
			}else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else if($action == 'finishedgroup'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($manager->num_rows) {
			    	if (isset($group_id)) {
			    		$group = $db->selectWhere('groups',[
						    [
						        'id'=>$group_id,
						        'cn'=>'='
						    ],
						]);
			    		if ($group->num_rows) {
			    			$update = $db->update('groups',[
							    'finished'=>1,
							],[
							    'id'=>$group_id,
							    'cn'=>'='
							]);
							$data['ok'] = true;
							if ($update) {
							    $data['code'] = 200;
							    $data['message'] = 'The group successfully completed the course.';
							}
							$groups = $db->selectWhere('groups',[
							    [
							        'id'=>0,
							        'cn'=>'>'
							    ],
							]);
							if ($groups->num_rows) {
							    $data['message'] = $data['message'] . " groups count: " . $groups->num_rows;
							    foreach ($groups as $key => $value) $data['result'][] = $value;
							}else{
							    $data['code'] = 405;
							    $data['message'] = $data['message'] . " There are no groups";
							}	
			    		}else{
			    			$data['code'] = 403;
			    			$data['message'] = 'group_id is invalid';
			    		}
			    	}else{
			    		$data['code'] = 403;
						$data['message'] = 'group_id is required';
			    	}
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
			}else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else if($action == 'changecoursedata'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($manager->num_rows) {
			    	if (isset($group_id)) {
			    		$group = $db->selectWhere('groups',[
					        [
					            'id'=>$group_id,
					            'cn'=>'='
					        ],
					    ]);
					    if ($group->num_rows) {
					    	if (isset($group_name) || isset($direction_id) || isset($teacher_id) || isset($room_id) || (isset($start_time) && isset($end_time))) {
					    		$group_fetch = mysqli_fetch_assoc($group);
				    			$bool = true;
					    		if (isset($direction_id)) {
					    			$direction = $db->selectWhere('directions',[
									    [
									        'id'=>$direction_id,
									        'cn'=>'='
									    ],
									]);
									if ($direction->num_rows) {
										
									}else{
										$bool = false;
										$data['code'] = 403;
					    				$data['message'] = 'direction_id is invalid';
									}
					    		}
					    		if (isset($teacher_id)) {
					    			$teacher = $db->selectWhere('teachers',[
									    [
									        'id'=>$teacher_id,
									        'cn'=>'='
									    ],
									]);
									if ($teacher->num_rows) {
										
									}else{
										$bool = false;
										$data['code'] = 403;
					    				$data['message'] = 'teacher_id is invalid';
									}
					    		}
					    		if (isset($room_id)) {
					    			$room = $db->selectWhere('rooms',[
									    [
									        'id'=>$room_id,
									        'cn'=>'='
									    ],
									]);
									if ($room->num_rows) {
										
									}else{
										$bool = false;
										$data['code'] = 403;
					    				$data['message'] = 'room_id is invalid';
									}
					    		}
					    		$group_name = $group_name ? $group_name : $group_fetch['group_name'];
					    		$direction_id = $direction_id ? $direction_id : $group_fetch['direction_id'];
					    		$teacher_id = $teacher_id ? $teacher_id : $group_fetch['teacher_id'];
					    		$room_id = $room_id ? $room_id : $group_fetch['room_id'];
					    		$start_time = $start_time ? $start_time : $group_fetch['start_time'];
					    		$end_time = $end_time ? $end_time : $group_fetch['end_time'];
						    	if ($bool) {
									$update = $db->update('groups',[
									    'group_name'=>$group_name,
									    'direction_id'=>$direction_id,
									    'teacher_id'=>$teacher_id,
									    'room_id'=>$room_id,
									    'start_time'=>$start_time,
									    'end_time'=>$end_time
									],[
									    'id'=>$group_id,
									    'cn'=>'='
									]);
									$data['ok'] = true;
									if ($update) {
									    $data['code'] = 200;
									    $data['message'] = 'Course data has been changed successfully.';
									}
									$groups = $db->selectWhere('groups',[
									    [
									        'id'=>0,
									        'cn'=>'>'
									    ],
									]);
									if ($groups->num_rows) {
									    $data['message'] = $data['message'] . " groups count: " . $groups->num_rows;
									    foreach ($groups as $key => $value) $data['result'][] = $value;
									}else{
									    $data['code'] = 405;
									    $data['message'] = $data['message'] . " There are no groups";
									}

						    	}
				    		}else{
					    		$data['code'] = 403;
								$data['message'] = 'One of these is required (group_name, direction_id, teacher_id, room_id, (start_time && end_time))';
					    	}
					    }else{
					    	$data['code'] = 403;
			    			$data['message'] = 'group_id is invalid';
					    }
			    	}else{
			    		$data['code'] = 402;
			    		$data['message'] = 'group_id is required';
			    	}	
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
			}else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else if($action == 'addstudenttogroup'){
			if (isset($token)) {
				$manager = $db->selectWhere('manager',[
			        [
			            'token'=>$token,
			            'cn'=>'='
			        ],
			    ]);
			    if ($manager->num_rows) {
			    	if (isset($student_id) && isset($group_id)) {
			    		$student = $db->selectWhere('registred',[
					        [
					            'id'=>$student_id,
					            'cn'=>'='
					        ],
					    ]);
					    if ($student->num_rows) {
					    	$group = $db->selectWhere('groups',[
						        [
						            'id'=>$group_id,
						            'cn'=>'='
						        ],
						    ]);
						    if ($group->num_rows) {
						    	$ins = $db->insertInto('students',[
								    'student_id'=>$student_id,
								    'group_id'=>$group_id,
								]);

								$data['code'] = 200;
								$data['message'] = 'Student joined the group';

								if (!$ins) {
								    $data['code'] = 500;
								    $data['message'] = 'Insert error: 500 set interval error';
								}

								$data['ok'] = true;
								$students = $db->selectWhere('students',[
								    [
								        'id'=>0,
								        'cn'=>'>'
								    ],
								]);
								foreach ($students as $key => $value) $data['result'][$key] = $value;
						    }else{
						    	$data['code'] = 403;
				    			$data['message'] = 'group_id is invalid';
						    }
					    }else{
					    	$data['code'] = 403;
			    			$data['message'] = 'student_id is invalid';
					    }
			    	}else{
			    		$data['code'] = 403;
						$data['message'] = 'student_id, group_id are required';
			    	}
			    }else{
			    	$data['code'] = 403;
			    	$data['message'] = 'token is invalid';
			    }
			}else{
				$data['code'] = 402;
			    $data['message'] = 'token is required';
			}
		}else{
			$data['code'] = 401;
            $data['message'] = 'Method not found';
		}
	}else{
		$data['code'] = 400;
		$data['message'] = "Method not allowed. Allowed Method: POST";
	}
	unset($data['result']['pass_word']);
	echo json_encode($data,  JSON_PRETTY_PRINT);
?> 