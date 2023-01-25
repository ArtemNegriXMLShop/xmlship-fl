// db.auth('root', 'rootAdmin')

db = db.getSiblingDB('logs')

db.createCollection('parcels');

db.createUser({
    user: 'xmlshop_user',
    pwd: 'xmlshop_password',
    roles: [
        {
            role: 'root',
            db: 'logs',
        },
    ],
});