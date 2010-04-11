# convert blank to None, good for optional ForeignKey field
def blank2None(str):
    if str == '':
        return None
    return str
