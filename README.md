## KannelConfig

Read Kannel configuration and parse it to array struct.

# example

a config file like this

    group = core
    admin-port = 13000
    smsbox-port = 13001
    admin-password = passwda

    # smsc center1 connection
    group = smsc
    smsc = smpp
    smsc-id = "Vodafone"

    is transformed into an array like that:

    $config = [
      'core' => [
        'admin-port' => 13000 ,
        'smsbox-port' => 13001 ,
        'admin-password' => 'passwda'
      ],

      'smsc' => [
         0 => [
           'smsc' => 'smpp' ,
           'smsc-id' => 'Vvodafone'
         ]
      ]
    ]
