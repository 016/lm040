<?php

    $errorCodeMapping = []; 
    
    //001-49 restful OBJ level
    $errorCodeMapping['400001'] = 'model name not load';
    $errorCodeMapping['404002'] = 'no pk attributes found in db.';
    $errorCodeMapping['401003'] = 'access token not found in db, validate fail';
    $errorCodeMapping['400004'] = 'request path not allow by api';
    $errorCodeMapping['400005'] = 'input data load fail';
    
    //60-79 RBAC
    $errorCodeMapping['404060'] = 'user not load. in RBAC check';
    $errorCodeMapping['403061'] = 'RBAC check fail';
    
    //80-99 response
    $errorCodeMapping['404080'] = 'create save fail';
    $errorCodeMapping['404081'] = 'replace fail';
    $errorCodeMapping['404082'] = 'update fail';
    $errorCodeMapping['404083'] = 'massive replace fail';
    
    //100-199 resource level
    $errorCodeMapping['400100'] = 'resource id not found in url';
    $errorCodeMapping['404101'] = 'resource validate fail';
    $errorCodeMapping['404102'] = 'resource validate field name missing';
    
    //200 - 299 asso resource level
    $errorCodeMapping['404200'] = 'asso model name not found.';
    $errorCodeMapping['404201'] = 'asso PK attributes not found in db.';
    $errorCodeMapping['400202'] = 'asso id not found in url';
    $errorCodeMapping['404203'] = 'asso level resource validate fail';
    $errorCodeMapping['404204'] = 'asso level asso validate field name missing';
    $errorCodeMapping['404205'] = 'asso level asso validate fail';
    
    //300 - 339 login fail
    $errorCodeMapping['400300'] = 'url params error';
    $errorCodeMapping['400301'] = 'client data wrong400301';
    $errorCodeMapping['404302'] = 'captcha is not available';
    $errorCodeMapping['404303'] = 'captcha input check fail';
    $errorCodeMapping['404304'] = 'user data not load';
    $errorCodeMapping['404305'] = 'password validate fail';
    $errorCodeMapping['404306'] = 'refresh token not found';
    $errorCodeMapping['404307'] = 'sms code wrong';
    $errorCodeMapping['404330'] = 'something wrong in login with access_token not exist at the end.';
    
    //340 - 359 Me
    $errorCodeMapping['400340'] = 'input passwords wrong.';
    $errorCodeMapping['403341'] = 'old password wrong';
    $errorCodeMapping['404342'] = 'email address already validated';
    
    //360-389 Reg
    $errorCodeMapping['404360'] = 'validate fail';
    $errorCodeMapping['404361'] = 'invite code invalid';
    $errorCodeMapping['404366'] = 'captcha load fail';
    $errorCodeMapping['404367'] = 'captcha input wrong';
    
    //1100 - 1160 User Common
    $errorCodeMapping['4041100'] = 'u_username required';
    $errorCodeMapping['4041101'] = 'u_password required';
    $errorCodeMapping['4041108'] = 'u_inputPassword required';
    $errorCodeMapping['4041102'] = 'u_displayName required';
    $errorCodeMapping['4041103'] = 'cap_input required';
    $errorCodeMapping['4041104'] = 'cap_id required';
    $errorCodeMapping['4041105'] = 'u_inviteCode required';
    $errorCodeMapping['4041106'] = 'u_username unique';
    $errorCodeMapping['4041107'] = 'u_displayName unique';
    $errorCodeMapping['4041109'] = 'u_phone unique';
        
    return $errorCodeMapping;