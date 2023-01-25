// db.auth('root', 'rootAdmin')

db = db.getSiblingDB('logs')

db.createCollection('parcels');

db.createUser({
    user: 'xml_user',
    pwd: 'xml_password',
    roles: [
        {
            role: 'root',
            db: 'logs',
        },
    ],
});