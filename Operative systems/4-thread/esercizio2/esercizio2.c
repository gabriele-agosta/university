#include <stdio.h>
#include <pthread.h>

typedef struct{
    int lunghezza;
    char carattere;
} argomenti;


void* print_xs(void *args){
    argomenti *dati = (argomenti*)(args);

    for(int j=0; j < dati->lunghezza; j++){
        printf("%c", dati->carattere);
    }

    return NULL;
}


int main(){
    pthread_t thread;
    argomenti arg = {400, 'x'};

    pthread_create(&thread, NULL, &print_xs, (void *)(&arg));

    for(int i=0; i < arg.lunghezza; i++){
        printf("o");
    }

    return 1;
}
