import mysql.connector


# Setup stuff for the mySQL connector
def create_cnx():
    cnx = mysql.connector.connect(
        user='macse_rberman',
        password='npUJgHoFmV2U3xKvtpKt',
        host='speak-easy.me',
        database='macse_datacollection'
    )
    return cnx


# Setup stuff for the mySQL connector
def create_cursor(cnx):
    cursor = cnx.cursor()
    return cursor


def sample(cursor):
    query = "SELECT id, email FROM interestscollection WHERE soccer = True"
    cursor.execute(query)
    for (id, email) in cursor:
        print(id)
        print(email)



def main():
    cnx = create_cnx()
    cursor = create_cursor(cnx)

    sample(cursor)

    cursor.close()
    cnx.close()

main()